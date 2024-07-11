<?php

namespace StatamicRadPack\Runway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Statamic\Fields\Field;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;

class Relationships
{
    public function __construct(protected Model $model, protected array $data = [])
    {
    }

    public static function for(Model $model): self
    {
        return new static($model);
    }

    public function with(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function save(): void
    {
        $this->model->runwayResource()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->fieldtype() instanceof HasManyFieldtype)
            ->each(function (Field $field): void {
                $relationshipName = $this->model->runwayResource()->eloquentRelationships()->get($field->handle());

                match (get_class($this->model->{$relationshipName}())) {
                    HasMany::class => $this->saveHasManyRelationship($field, $this->data[$field->handle()]),
                    BelongsToMany::class => $this->saveBelongsToManyRelationship($field, $this->data[$field->handle()]),
                };
            });
    }

    protected function saveHasManyRelationship(Field $field, array $data): void
    {
        $deleted = [];
        $relatedResource = Runway::findResource($field->fieldtype()->config('resource'));

        /** @var HasMany $relationship */
        $relationship = $this->model->{$field->handle()}();

        // Delete any deleted models
        collect($relationship->get())
            ->reject(fn ($relatedModel) => in_array($relatedModel->id, $data))
            ->each(function ($relatedModel) use ($relatedResource, &$deleted) {
                $deleted[] = $relatedModel->{$relatedResource->primaryKey()};

                $relatedModel->delete();
            });

        // Add anything new
        collect($data)
            ->reject(fn ($relatedId) => $relationship->get()->pluck($relatedResource->primaryKey())->contains($relatedId))
            ->reject(fn ($relatedId) => in_array($relatedId, $deleted))
            ->each(function ($relatedId) use ($relatedResource, $relationship) {
                $relatedModel = $relatedResource->model()->find($relatedId);

                $relatedModel->update([
                    $relationship->getForeignKeyName() => $this->model->{$relatedResource->primaryKey()},
                ]);
            });

        // If reordering is enabled, update all models with their new sort order.
        if ($field->fieldtype()->config('reorderable') && $orderColumn = $field->fieldtype()->config('order_column')) {
            collect($data)
                ->each(function ($relatedId, $index) use ($relatedResource, $orderColumn) {
                    $relatedModel = $relatedResource->model()->find($relatedId);

                    if ($relatedModel->{$orderColumn} === $index) {
                        return;
                    }

                    $relatedModel->update([
                        $orderColumn => $index,
                    ]);
                });
        }
    }

    protected function saveBelongsToManyRelationship(Field $field, array $data): void
    {
        // When Reordering is enabled, we need to change the format of the $data array. The key should
        // be the foreign key and the value should be the pivot data (our sort order).
        if ($field->fieldtype()->config('reorderable') && $orderColumn = $field->fieldtype()->config('order_column')) {
            $data = collect($data)
                ->mapWithKeys(function ($relatedId, $index) use ($orderColumn) {
                    return [$relatedId => [$orderColumn => $index]];
                })
                ->toArray();
        }

        $this->model->{$field->handle()}()->sync($data);
    }
}
