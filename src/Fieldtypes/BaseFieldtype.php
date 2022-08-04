<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Statamic\CP\Column;
use Statamic\Facades\Blink;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Relationship;

class BaseFieldtype extends Relationship
{
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $categories = ['relationship'];
    protected $formComponent = 'runway-publish-form';
    protected $component = 'runway-relationship';

    protected $formComponentProps = [
        'initialBlueprint' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'initialTitle' => 'title',
        'action' => 'action',
        'method' => 'method',
        'resourceHasRoutes' => 'resourceHasRoutes',
        'permalink' => 'permalink',
    ];

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
            'create' => [
                'display' => __('Allow Creating'),
                'instructions' => __('statamic::fieldtypes.entries.config.create'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'with' => [
                'display' => __('Eager Loaded Relationships'),
                'instructions' => 'Specify any relationships you wish to be eager loaded when this field is augmented.',
                'type' => 'list',
                'default' => [],
                'width' => 50,
            ],
        ];
    }

    // Provides the dropdown options
    public function getIndexItems($request)
    {
        $resource = Runway::findResource($this->config('resource'));

        $query = $resource->model()
            ->orderBy($resource->primaryKey(), 'ASC');

        if ($query->hasNamedScope('runwayListing')) {
            $query->runwayListing();
        }

        return $query
            ->when($request->search, function ($query) use ($request, $resource) {
                $searchQuery = $request->search;

                $query->when(
                    $query->hasNamedScope('runwaySearch'),
                    function ($query) use ($searchQuery) {
                        $query->runwaySearch($searchQuery);
                    },
                    function ($query) use ($searchQuery, $resource) {
                        $resource->blueprint()->fields()->items()->reject(function (array $field) {
                            return $field['field']['type'] === 'has_many'
                                || $field['field']['type'] === 'hidden';
                        })->each(function (array $field) use ($query, $searchQuery) {
                            $query->orWhere($field['handle'], 'LIKE', '%' . $searchQuery . '%');
                        });
                    }
                );
            })
            ->get()
            ->map(function ($record) use ($resource) {
                return collect($resource->listableColumns())
                    ->mapWithKeys(function ($columnKey) use ($record) {
                        $value = $record->{$columnKey};

                        // If this is a relationship, then we want to process the values...
                        if ($value instanceof EloquentCollection) {
                            $value = $value->map(function ($item) {
                                return $this->toItemArray($item);
                            })->values()->toArray();
                        }

                        return [$columnKey => $value];
                    })
                    ->merge([
                        'id' => $record->{$resource->primaryKey()},
                        'title' => $this->makeTitle($record, $resource),
                    ])
                    ->toArray();
            })
            ->filter()
            ->values();
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
                // In a many to many relation item is an array
                if (is_array($item)) {
                    $item = $item['id'] ?? null;
                }

                $record = $resource->model()->firstWhere($resource->primaryKey(), $item);
            } else {
                $record = $item;
            }

            if (! $record) {
                return null;
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
                    $eagerLoadingRelations = collect($this->config('with') ?? [])->join(',');

                    $record = Blink::once("Runway::{$this->config('resource')}::{$record}::{$eagerLoadingRelations}", function () use ($resource, $record) {
                        return $resource->model()
                            ->when($this->config('with'), function ($query) {
                                $query->with(Arr::wrap($this->config('with')));
                            })
                            ->firstWhere($resource->primaryKey(), $record);
                    });
                }

                if (! $record) {
                    return null;
                }

                return $resource->augment($record);
            })
            ->filter();

        if ($this->config('max_items') === 1) {
            return $result->first();
        }

        return $result->toArray();
    }

    // Provides the columns used if you're in 'Stacks' mode
    protected function getColumns()
    {
        $resource = Runway::findResource($this->config('resource'));
        $blueprint = $resource->blueprint();

        return collect($resource->listableColumns())
            ->map(function ($columnKey) use ($blueprint) {
                /** @var \Statamic\Fields\Field $field */
                $blueprintField = $blueprint->field($columnKey);

                return Column::make($columnKey)
                    ->fieldtype($blueprintField->fieldtype()->handle());
            })
            // ->merge([Column::make('title')])
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

        if (! $record) {
            return [
                'id' => $id,
                'title' => $id,
                'invalid' => true,
            ];
        }

        $editUrl = cp_route('runway.edit', [
            'resourceHandle' => $resource->handle(),
            'record' => $record->{$resource->routeKey()},
        ]);

        if ($this->config('mode') === 'table') {
            return collect($resource->listableColumns())
                ->mapWithKeys(function ($columnKey) use ($record) {
                    $value = $record->{$columnKey};

                    // If this is a relationship, then we want to process the values...
                    if ($value instanceof EloquentCollection) {
                        $value = $value->map(function ($item) {
                            return $this->toItemArray($item);
                        })->values()->toArray();
                    }

                    return [$columnKey => $value];
                })
                ->merge([
                    'id' => $record->{$resource->primaryKey()},
                    'title' => $this->makeTitle($record, $resource),
                    'edit_url' => $editUrl,
                    'editable' => User::current()->hasPermission("Edit {$resource->plural()}") || User::current()->isSuper(),
                    'viewable' => User::current()->hasPermission("View {$resource->plural()}") || User::current()->isSuper(),
                ])
                ->toArray();
        }

        return [
            'id'    => $record->getKey(),
            'title' => $this->makeTitle($record, $resource),
            'edit_url' => $editUrl,
        ];
    }

    protected function getCreatables()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            [
                'title' => $resource->singular(),
                'url' => cp_route('runway.create', [
                    'resourceHandle'  => $resource->handle(),
                ]),
            ],
        ];
    }

    protected function makeTitle($record, $resource): string
    {
        if (! $titleFormat = $this->config('title_format')) {
            $firstListableColumn = $resource->listableColumns()[0];

            return $record->{$firstListableColumn};
        }

        return Parse::template($titleFormat, $record);
    }
}
