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
                    HasMany::class => $this->saveHasManyRelationship($field, $this->data[$field->handle()] ?? null),
                    BelongsToMany::class => $this->saveBelongsToManyRelationship($field, $this->data[$field->handle()] ?? null),
                };
            });
    }

    protected function saveHasManyRelationship(Field $field, array $data): void
    {
        $relatedResource = Runway::findResource($field->fieldtype()->config('resource'));

        /** @var HasMany $relationship */
        $relationship = $this->model->{$field->handle()}();

        $deleted = $relationship->whereNotIn($relatedResource->primaryKey(), $data)->get()
            ->each->delete()
            ->map->getKey()
            ->all();

        $models = $relationship->get();

        collect($data)
            ->reject(fn ($id) => $models->pluck($relatedResource->primaryKey())->contains($id))
            ->reject(fn ($id) => in_array($id, $deleted))
            ->each(fn ($id) => $relatedResource->model()->find($id)->update([
                $relationship->getForeignKeyName() => $this->model->getKey(),
            ]));

        if ($field->fieldtype()->config('reorderable') && $orderColumn = $field->fieldtype()->config('order_column')) {
            collect($data)
                ->map(fn ($id) => $relatedResource->model()->find($id))
                ->reject(fn (Model $model, int $index) => $model->getAttribute($orderColumn) === $index)
                ->each(fn (Model $model, int $index) => $model->update([$orderColumn => $index]));
        }
    }

    protected function saveBelongsToManyRelationship(Field $field, array $data): void
    {
        if ($field->fieldtype()->config('reorderable') && $orderColumn = $field->fieldtype()->config('order_column')) {
            $data = collect($data)->mapWithKeys(fn ($id, $index) => [$id => [$orderColumn => $index]])->all();
        }

        $this->model->{$field->handle()}()->sync($data);
    }
}
