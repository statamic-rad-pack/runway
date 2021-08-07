<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;
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
            'resource' => [
                'display' => 'Resource',
                'instructions' => "Select the Runway resource you'd like to be selectable from this field.",
                'type' => 'select',
                'options' => collect(Runway::allResources())
                    ->mapWithKeys(function ($resource) {
                        return [$resource->handle() => $resource->name()];
                    })
                    ->toArray(),
                'width' => 50,
            ],
        ];
    }

    public function getIndexItems($request)
    {
        $resource = Runway::findResource($this->config('resource'));

        return $resource->model()
            ->orderBy($resource->primaryKey(), 'ASC')
            ->get()
            ->map(function ($record) use ($resource) {
                return collect($resource->listableColumns())
                    ->mapWithKeys(function ($columnKey) use ($record) {
                        return [$columnKey => $record->{$columnKey}];
                    })
                    ->merge(['id' => $record->{$resource->primaryKey()}])
                    ->toArray();
            })
            ->filter()->values();
    }

    public function preProcessIndex($data)
    {
        $resource = Runway::findResource($this->config('resource'));

        if (! $data) {
            return null;
        }

        if ($this->config('max_items') === 1) {
            $data = [$data];
        }

        return collect($data)->map(function ($item) use ($resource) {
            $column = $resource->listableColumns()[0];

            $fieldtype = $resource->blueprint()->field($column)->fieldtype();
            $record = $resource->model()->firstWhere($resource->primaryKey(), $item);

            $url = cp_route('runway.edit', [
                'resourceHandle' => $resource->handle(),
                'record' => $record->{$resource->routeKey()},
            ]);

            return [
                'id' => $record->{$resource->primaryKey()},
                'title' => $fieldtype->preProcessIndex($record->{$column}),
                'edit_url' => $url,
            ];
        });
    }

    public function augment($values)
    {
        $resource = Runway::findResource($this->config('resource'));

        $result = collect($values)->map(function ($recordId) use ($resource) {
            $record = $resource->model()->firstWhere($resource->primaryKey(), $recordId);

            return $resource->augment($record);
        });

        if ($this->config('max_items') === 1) {
            return $result->first();
        }

        return $result->toArray();
    }

    protected function getColumns()
    {
        $resource = Runway::findResource($this->config('resource'));

        return collect($resource->listableColumns())
            ->map(function ($columnKey) {
                return Column::make($columnKey);
            })
            ->merge([Column::make('title')])
            ->toArray();
    }

    protected function toItemArray($id)
    {
        $resource = Runway::findResource($this->config('resource'));
        $record = $resource->model()->firstWhere($resource->primaryKey(), $id);

        return [
            'id'    => $record->getKey(),
            'title' => $record->{collect($resource->listableColumns())->first()},
        ];
    }
}
