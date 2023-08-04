<?php

namespace DoubleThreeDigital\Runway\Query\Scopes\Filters\Fields;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Models extends FieldtypeFilter
{
    public function fieldItems()
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

    public function apply($query, $handle, $values)
    {
        $resource = Runway::findResourceByModel($query->getModel());

        $field = $values['field'];
        $operator = $values['operator'];
        $value = $values['value'];

        if ($operator === 'like') {
            $value = Str::ensureLeft($value, '%');
            $value = Str::ensureRight($value, '%');
        }

        $query->whereHas($resource->eagerLoadingRelations()->get($this->fieldtype->field()->handle()), function (Builder $query) use ($field, $operator, $value) {
            $query->where($field, $operator, $value);
        });
    }

    public function badge($values)
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
