<?php

namespace DoubleThreeDigital\Runway\Tags;

use DoubleThreeDigital\Runway\Support\ModelFinder;
use Statamic\Fields\Field;
use Statamic\Tags\Tags;

class RunwayTag extends Tags
{
    protected static $handle = 'runway';

    public function wildcard($model = null)
    {
        $model = ModelFinder::find(
            $this->params->has('model') ? $this->params->get('model') : $model
        );

        $blueprint = $model['blueprint'];

        $query = (new $model['model']())->query();

        if ($this->params->has('where') && $where = $this->params->get('where')) {
            $query->where(explode(':', $where)[0], explode(':', $where)[1]);
        }

        if ($this->params->has('sort')) {
            $sortColumn = explode(':', $this->params->get('sort'))[0];
            $sortDirection = explode(':', $this->params->get('sort'))[1];

            $query->orderBy($sortColumn, $sortDirection);
        }

        if ($this->params->has('limit')) {
            return $this->augmentRecords(
                $query->paginate($this->params->get('limit'))->all(),
                $blueprint
            );
        }

        return $this->augmentRecords($query->get(), $blueprint);
    }

    protected function augmentRecords($query, $blueprint)
    {
        return collect($query)
            ->map(function ($record, $key) use ($blueprint) {
                return $this->augmentRecord($record, $blueprint);
            })
            ->toArray();
    }

    protected function augmentRecord($record, $blueprint)
    {
        return collect($record)
            ->map(function ($value, $key) use ($blueprint) {
                if ($value instanceof \Carbon\Carbon) {
                    return $value->format('Y-m-d H:i');
                }

                if ($blueprint->hasField($key)) {
                    return $blueprint->field($key)->fieldtype()->augment($value);
                }

                return $value;
            })
            ->toArray();

        $values = [];
        $blueprintFields = collect($blueprint->fields()->all())->map(function (Field $field) {
            return $field->fieldtype();
        });

        foreach ($blueprintFields as $fieldKey => $fieldtype) {
            $value = $record->{$fieldKey};

            if ($value instanceof \Carbon\Carbon) {
                $value = $value->format('Y-m-d H:i');
            }

            $values[$fieldKey] = $fieldtype->augment($value);
        }

        return $values;
    }
}
