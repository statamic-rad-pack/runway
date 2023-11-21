<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Query\Scopes\Filters\Fields\Models;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\CP\Column;
use Statamic\Facades\Blink;
use Statamic\Facades\Parse;
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
        'resource' => 'resource',
        'breadcrumbs' => 'breadcrumbs',
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
                'display' => __('Resource'),
                'instructions' => __("Select the Runway resource you'd like to be selectable from this field."),
                'type' => 'select',
                'options' => collect(Runway::allResources())
                    ->mapWithKeys(fn ($resource) => [$resource->handle() => $resource->name()])
                    ->toArray(),
                'width' => 50,
                'validate' => 'required',
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
                'instructions' => __('Specify any relationships you wish to be eager loaded when this field is augmented.'),
                'type' => 'list',
                'default' => [],
                'width' => 50,
            ],
        ];
    }

    public function getIndexItems($request)
    {
        $resource = Runway::findResource($this->config('resource'));

        $query = $resource->model()->orderBy($resource->orderBy(), $resource->orderByDirection());

        $query->when($query->hasNamedScope('runwayListing'), fn ($query) => $query->runwayListing());
        $query->when($request->search, fn ($query) => $query->runwaySearch($request->search));

        $items = $request->boolean('paginate', true)
            ? $query->paginate()
            : $query->get();

        $items
            ->transform(function ($record) use ($resource) {
                return $resource->listableColumns()
                    ->mapWithKeys(function ($columnKey) use ($record) {
                        $value = $record->{$columnKey};

                        // When $value is an Eloquent Collection, we want to map over each item & process its values.
                        if ($value instanceof EloquentCollection) {
                            $value = $value->map(fn ($item) => $this->toItemArray($item))->values()->toArray();
                        }

                        return [$columnKey => $value];
                    })
                    ->merge([
                        'id' => $record->{$resource->primaryKey()},
                        'title' => $this->makeTitle($record, $resource),
                    ])
                    ->toArray();
            });

        return $request->boolean('paginate', true)
            ? $items
            : $items->filter()->values();
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
            $column = $resource->titleField();

            $fieldtype = $resource->blueprint()->field($column)->fieldtype();

            if (! $item instanceof Model) {
                // In a Many to Many relationship, $item is an array.
                if (is_array($item)) {
                    $item = $item['id'] ?? null;
                }

                // And sometimes, $item will be a Collection.
                if ($item instanceof Collection) {
                    $item = $item->first()->{$resource->primaryKey()} ?? null;
                }

                $record = $resource->model()->firstWhere($resource->primaryKey(), $item);
            } else {
                $record = $item;
            }

            if (! $record) {
                return null;
            }

            $url = cp_route('runway.edit', [
                'resource' => $resource->handle(),
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

        if ($values instanceof HasMany) {
            $results = $values
                ->get()
                ->map->toAugmentedArray()
                ->filter();

            if ($this->config('max_items') === 1) {
                return $results->first();
            }

            return $results;
        }

        if ($values instanceof BelongsTo) {
            $results = $values
                ->get()
                ->map->toAugmentedArray()
                ->filter();

            if ($this->config('max_items') === 1) {
                return $results->first();
            }

            return $results;
        }

        $values = Arr::wrap($values);

        $results = collect($values)
            ->map(function ($item) use ($resource) {
                if (is_array($item) && Arr::has($item, $resource->primaryKey())) {
                    return Arr::get($item, $resource->primaryKey());
                }

                return $item;
            })
            ->map(function ($record) use ($resource) {
                if (! $record instanceof Model) {
                    $eagerLoadingRelationships = collect($this->config('with') ?? [])->join(',');

                    $record = Blink::once("Runway::{$this->config('resource')}::{$record}}::{$eagerLoadingRelationships}", function () use ($resource, $record) {
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

                return $record->toAugmentedArray();
            })
            ->filter();

        if ($this->config('max_items') === 1) {
            return $results->first();
        }

        return $results->toArray();
    }

    public function shallowAugment($values)
    {
        $resource = Runway::findResource($this->config('resource'));

        if ($values instanceof HasMany) {
            $results = $values
                ->get()
                ->map->toShallowAugmentedArray()
                ->filter();

            if ($this->config('max_items') === 1) {
                return $results->first();
            }

            return $results;
        }

        $values = Arr::wrap($values);

        $results = collect($values)
            ->map(function ($record) use ($resource) {
                if (! $record instanceof Model) {
                    $eagerLoadingRelations = collect($this->config('with') ?? [])->join(',');

                    $record = Blink::once("Runway::{$this->config('resource')}::{$record}}::{$eagerLoadingRelations}", function () use ($resource, $record) {
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

                return $record->toShallowAugmentedArray();
            })
            ->filter();

        if ($this->config('max_items') === 1) {
            return $results->first();
        }

        return $results->toArray();
    }

    protected function getColumns()
    {
        $resource = Runway::findResource($this->config('resource'));
        $blueprint = $resource->blueprint();

        return $resource->listableColumns()
            ->map(function ($columnKey, $index) use ($blueprint) {
                /** @var \Statamic\Fields\Field $field */
                $blueprintField = $blueprint->field($columnKey);

                return Column::make()
                    ->field($blueprintField->handle())
                    ->label(__($blueprintField->display()))
                    ->fieldtype($blueprintField->fieldtype()->indexComponent())
                    ->listable($blueprintField->isListable())
                    ->defaultVisibility($blueprintField->isVisibleOnListing())
                    ->visible($blueprintField->isVisibleOnListing())
                    ->sortable($blueprintField->isSortable())
                    ->defaultOrder($index + 1);
            })
            ->toArray();
    }

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
            'resource' => $resource->handle(),
            'record' => $record->{$resource->routeKey()},
        ]);

        return [
            'id' => $record->getKey(),
            'title' => $this->makeTitle($record, $resource),
            'edit_url' => $editUrl,
        ];
    }

    protected function getCreatables()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [[
            'title' => $resource->singular(),
            'url' => cp_route('runway.create', [
                'resource' => $resource->handle(),
            ]),
        ]];
    }

    protected function makeTitle($record, $resource): ?string
    {
        if (! $titleFormat = $this->config('title_format')) {
            $firstListableColumn = $resource->titleField();

            return $record->{$firstListableColumn};
        }

        return Parse::template($titleFormat, $record);
    }

    public function filter()
    {
        return new Models($this);
    }
}
