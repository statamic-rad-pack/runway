<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\AugmentedRecord;
use DoubleThreeDigital\Runway\Support\ModelFinder;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class BaseFieldtype extends Relationship
{
    protected $canCreate = false;
    protected $categories = ['relationship'];

    protected function configFieldItems(): array
    {
        return [
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                'type' => 'radio',
                'default' => 'default',
                'options' => [
                    'default' => __('Stack Selector'),
                    'select' => __('Select Dropdown'),
                    'typeahead' => __('Typeahead Field'),
                ],
                'width' => 50,
            ],
            'model' => [
                'display' => 'Eloquent Model',
                'instructions' => "Select the Eloquent model you'd like to use for this field.",
                'type' => 'select',
                'options' => collect(ModelFinder::all())
                    ->mapWithKeys(function ($model) {
                        return [$model['_handle'] => $model['name']];
                    })
                    ->toArray(),
                'width' => 50,
            ],
        ];
    }

    public function getIndexItems($request)
    {
        $model = ModelFinder::find($this->config('model'));

        return (new $model['model']())
            ->orderBy($model['listing_sort']['column'], $model['listing_sort']['direction'])
            ->get()
            ->map(function ($record) use ($model) {
                return collect($model['listing_columns'])
                    ->mapWithKeys(function ($columnKey) use ($record) {
                        return [$columnKey => $record->{$columnKey}];
                    })
                    ->merge(['id' => $record->{$model['primary_key']}])
                    ->toArray();
            })
            ->filter()->values();
    }

    public function getItemData($values)
    {
        $model = ModelFinder::find($this->config('model'));

        return collect($values)
            ->map(function ($value) use ($model) {
                $record = (new $model['model']())->firstWhere($model['primary_key'], $value);

                return [
                    'id' => $value,
                    'title' => $record->{collect($model['listing_columns'])->first()},
                ];
            });
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return null;
        }

        $model = ModelFinder::find($this->config('model'));

        return collect($data)
            ->map(function ($item) use ($model) {
                $record = (new $model['model']())->firstWhere($model['primary_key'], $item);

                return [
                    'id' => $item,
                    'title' => $record->{collect($model['listing_columns'])->first()},
                ];
            });
    }

    public function augment($values)
    {
        $model = ModelFinder::find($this->config('model'));

        return collect($values)->map(function ($recordId) use ($model) {
            $record = (new $model['model']())->firstWhere($model['primary_key'], $recordId);

            return AugmentedRecord::augment($record, $model['blueprint']);
        })->filter()->values();
    }

    protected function getColumns()
    {
        $model = ModelFinder::find($this->config('model'));

        return collect($model['listing_columns'])
            ->map(function ($columnKey) {
                return Column::make($columnKey);
            })
            ->toArray();
    }

    protected function toItemArray($id)
    {
        $model = ModelFinder::find($this->config('model'));
        $record = (new $model['model']())->firstWhere($model['primary_key'], $id);

        return [
            'id' => $record->{$model['primary_key']},
            'title' => $record->{collect($model['listing_columns'])->first()},
        ];
    }
}
