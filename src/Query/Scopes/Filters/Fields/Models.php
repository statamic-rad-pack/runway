<?php

namespace DoubleThreeDigital\Runway\Query\Scopes\Filters\Fields;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Models extends FieldtypeFilter
{
    public function fieldItems(): array
    {
        $resource = Runway::findResource($this->fieldtype->config('resource'));

        return [
            'field' => [
                'type' => 'select',
                'options' => [
                    $resource->primaryKey() => __(Str::upper($resource->primaryKey())),
                    $resource->titleField() => $resource->blueprint()->field($resource->titleField())->display(),
                ],
                'default' => $resource->titleField(),
            ],
            'operator' => [
                'type' => 'select',
                'options' => [
                    'like' => __('Contains'),
                    'not like' => __('Doesn\'t contain'),
                    '=' => __('Is'),
                    '!=' => __('Isn\'t'),
                ],
                'default' => 'like',
            ],
            'value' => [
                'type' => 'text',
                'placeholder' => __('Value'),
                'if' => [
                    'operator' => 'not empty',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values): void
    {
        $field = $values['field'];
        $operator = $values['operator'];
        $value = $values['value'];

        if ($operator === 'like' || $operator === 'not like') {
            $value = Str::ensureLeft($value, '%');
            $value = Str::ensureRight($value, '%');
        }

        $relatedResource = Runway::findResource($this->fieldtype->config('resource'));

        $ids = $relatedResource->model()->query()
            ->where($field, $operator, $value)
            ->select($relatedResource->primaryKey())
            ->get()
            ->pluck($relatedResource->primaryKey())
            ->toArray();

        // When we're dealing with an Eloquent query builder, we can take advantage of `whereHas` to filter.
        // Otherwise, we'll just filter the IDs directly.
        if (method_exists($query, 'getModel')) {
            // This is the resource of the Eloquent model that the filter is querying.
            $queryingResource = Runway::findResourceByModel($query->getModel());

            $query->whereHas($queryingResource->eloquentRelationships()->get($this->fieldtype->field()->handle()), function (Builder $query) use ($relatedResource, $ids) {
                $query->whereIn($relatedResource->primaryKey(), $ids);
            });
        } else {
            $query->whereIn($this->fieldtype->field()->handle(), $ids);
        }
    }

    public function badge($values): string
    {
        $field = $this->fieldtype->field()->display();
        $selectedField = $values['field'];
        $operator = $values['operator'];
        $translatedField = Arr::get($this->fieldItems(), "field.options.{$selectedField}");
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = $values['value'];

        return $field.' '.$translatedField.' '.strtolower($translatedOperator).' '.$value;
    }
}
