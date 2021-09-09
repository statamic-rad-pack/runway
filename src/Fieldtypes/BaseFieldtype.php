<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\Post;
use Illuminate\Database\Eloquent\Model;
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

    // Provides the dropdown options
    public function getIndexItems($request)
    {
        $resource = Runway::findResource($this->config('resource'));

        return $resource->model()
            ->orderBy($resource->primaryKey(), 'ASC')
            ->get()
            ->map(function ($record) use ($resource) {
                $firstListableColumn = $resource->listableColumns()[0];

                return collect($resource->listableColumns())
                    ->mapWithKeys(function ($columnKey) use ($record) {
                        return [$columnKey => $record->{$columnKey}];
                    })
                    ->merge([
                        'id' => $record->{$resource->primaryKey()},
                        'title' => $record->{$firstListableColumn},
                    ])
                    ->toArray();
            })
            ->filter()->values();
    }

    // This shows the values in the listing table
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

            if (! $item instanceof Model) {
                $record = $resource->model()->firstWhere($resource->primaryKey(), $item);
            } else {
                $record = $item;
            }

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

    // Augments the value for front-end use
    public function augment($values)
    {
        $resource = Runway::findResource($this->config('resource'));

        $result = collect($values)
            ->map(function ($item) use ($resource) {
                if (is_array($item) && isset($item[$resource->primaryKey()])) {
                    return $item[$resource->primaryKey()];
                }

                return $item;
            })

            ->map(function ($record) use ($resource) {
                if (! $record instanceof Model) {
                    $record = $resource->model()->firstWhere($resource->primaryKey(), $record);
                }

                return $resource->augment($record);
            });

        if ($this->config('max_items') === 1) {
            return $result->first();
        }

        return $result->toArray();
    }

    // Provides the columns used if you're in 'Stacks' mode
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

    // Provides the initial state after loading the fieldtype on a saved entry/model
    protected function toItemArray($id)
    {
        $resource = Runway::findResource($this->config('resource'));

        if (! $id instanceof Model) {
            $record = $resource->model()->firstWhere($resource->primaryKey(), $id);
        } else {
            $record = $id;
        }

        return [
            'id'    => $record->getKey(),
            'title' => $record->{collect($resource->listableColumns())->first()},
        ];
    }
}
