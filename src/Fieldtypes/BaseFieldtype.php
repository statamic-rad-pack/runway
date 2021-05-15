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
            ->orderBy($resource->listingSort()['column'], $resource->listingSort()['direction'])
            ->get()
            ->map(function ($record) use ($resource) {
                return collect($resource->listingColumns())
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
        if (! $data) {
            return null;
        }

        $resource = Runway::findResource($this->config('resource'));

        return collect($data)
            ->map(function ($item) use ($resource) {
                $record = $resource->model()->firstWhere($resource->primaryKey(), $item);

                return $record->{collect($resource->listingColumns())->first()};
            })
            ->join(', ');
    }

    public function augment($values)
    {
        $resource = Runway::findResource($this->config('resource'));

        return collect($values)->map(function ($recordId) use ($resource) {
            $record = $resource->model()->firstWhere($resource->primaryKey(), $recordId);

            return $resource->augment($record);
        })->toArray();
    }

    protected function getColumns()
    {
        $resource = Runway::findResource($this->config('resource'));

        return collect($resource->listingColumns())
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
            'id'    => $record->id,
            'title' => $record->{collect($resource->listingColumns())->first()},
        ];
    }
}
