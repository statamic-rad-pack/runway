<?php

namespace StatamicRadPack\Runway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Statamic\Fields\Field;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;

class Relationships
{
    public function __construct(protected Model $model, protected array $values = []) {}

    public static function for(Model $model): self
    {
        return new static($model);
    }

    public function with(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function save(): void
    {
        $this->model->runwayResource()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->fieldtype() instanceof HasManyFieldtype)
            ->each(function (Field $field): void {
                $relationshipName = $this->model->runwayResource()->eloquentRelationships()->get($field->handle());

                match (get_class($relationship = $this->model->{$relationshipName}())) {
                    HasMany::class => $this->saveHasManyRelationship($field, $relationship, $this->values[$field->handle()] ?? []),
                    BelongsToMany::class => $this->saveBelongsToManyRelationship($field, $relationship,$this->values[$field->handle()] ?? []),
                };
            });
    }

    protected function saveHasManyRelationship(Field $field, Relation $relationship, array $values): void
    {
        $relatedResource = Runway::findResource($field->fieldtype()->config('resource'));

        $deleted = $relationship->whereNotIn($relatedResource->primaryKey(), $values)->get()
            ->each->delete()
            ->map->getKey()
            ->all();

        $models = $relationship->get();

        collect($values)
            ->reject(fn ($id) => $models->pluck($relatedResource->primaryKey())->contains($id))
            ->reject(fn ($id) => in_array($id, $deleted))
            ->each(fn ($id) => $relatedResource->model()->find($id)->update([
                $relationship->getForeignKeyName() => $this->model->getKey(),
            ]));

        if ($field->fieldtype()->config('reorderable') && $orderColumn = $field->fieldtype()->config('order_column')) {
            collect($values)
                ->map(fn ($id) => $relatedResource->model()->find($id))
                ->reject(fn (Model $model, int $index) => $model->getAttribute($orderColumn) === $index)
                ->each(fn (Model $model, int $index) => $model->update([$orderColumn => $index]));
        }
    }

    protected function saveBelongsToManyRelationship(Field $field, Relation $relationship, array $values): void
    {
        if ($field->fieldtype()->config('reorderable') && $orderColumn = $field->fieldtype()->config('order_column')) {
            $values = collect($values)->mapWithKeys(fn ($id, $index) => [$id => [$orderColumn => $index]])->all();
        }

        $relationship->sync($values);
    }
}
