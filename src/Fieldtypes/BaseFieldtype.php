<?php

namespace StatamicRadPack\Runway\Fieldtypes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\CP\Column;
use Statamic\Facades\Blink;
use Statamic\Facades\Parse;
use Statamic\Fieldtypes\Relationship;
use Statamic\Query\Builder as BaseStatamicBuilder;
use Statamic\Search\Result;
use StatamicRadPack\Runway\Http\Resources\CP\FieldtypeModel;
use StatamicRadPack\Runway\Http\Resources\CP\FieldtypeModels;
use StatamicRadPack\Runway\Query\Scopes\Filters\Fields\Models;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class BaseFieldtype extends Relationship
{
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $categories = ['relationship'];
    protected $formComponent = 'runway-publish-form';

    protected $formComponentProps = [
        'initialReference' => 'reference',
        'initialBlueprint' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'initialTitle' => 'title',
        'resource' => 'resource',
        'breadcrumbs' => 'breadcrumbs',
        'initialActions' => 'actions',
        'method' => 'method',
        'initialReadOnly' => 'readOnly',
        'initialPermalink' => 'permalink',
        'canManagePublishState' => 'canManagePublishState',
        'resourceHasRoutes' => 'resourceHasRoutes',
        'revisionsEnabled' => 'revisionsEnabled',
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
                'instructions' => __('Specify the Runway Resource to be used for this field.'),
                'type' => 'select',
                'options' => collect(Runway::allResources())
                    ->mapWithKeys(fn ($resource) => [$resource->handle() => $resource->name()])
                    ->toArray(),
                'width' => 50,
                'validate' => 'required',
            ],
            'relationship_name' => [
                'display' => __('Relationship Name'),
                'instructions' => __('The name of the Eloquent Relationship this field should use. When left empty, Runway will attempt to guess it based on the field handle.'),
                'type' => 'text',
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
                'instructions' => __('Specify any relationship which should be eager loaded when this field is augmented.'),
                'type' => 'list',
                'default' => [],
                'width' => 50,
            ],
            'title_format' => [
                'display' => __('Title Format'),
                'instructions' => __('Configure the title format used for displaying results in the fieldtype. You can use Antlers to pull in model data.'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }

    public function icon()
    {
        return File::get(__DIR__.'/../../resources/svg/database.svg');
    }

    public function getIndexItems($request)
    {
        $resource = Runway::findResource($this->config('resource'));

        $query = $resource->model()->newQuery();

        $query->when($query->hasNamedScope('runwayListing'), fn ($query) => $query->runwayListing());

        $searchQuery = $request->search ?? false;

        $query = $this->applySearch($resource, $query, $searchQuery);

        $query->when(method_exists($query, 'getQuery') && $query->getQuery()->orders, function ($query) use ($request, $resource) {
            if ($orderBy = $request->input('sort')) {
                // The stack selector always uses `title` as the default sort column, but
                // the "title field" for the model might be a different column so we need to convert it.
                $sortColumn = $orderBy === 'title' ? $resource->titleField() : $orderBy;

                $query->reorder($sortColumn, $request->input('order'));
            }
        }, fn ($query) => $query->orderBy($resource->orderBy(), $resource->orderByDirection()));

        $results = ($paginate = $request->boolean('paginate', true)) ? $query->paginate() : $query->get();

        $items = $results->map(fn ($item) => $item instanceof Result ? $item->getSearchable() : $item);

        return $paginate ? $results->setCollection($items) : $items;
    }

    public function getResourceCollection($request, $items)
    {
        $resource = Runway::findResource($this->config('resource'));

        return (new FieldtypeModels($items, $this))
            ->runwayResource($resource)
            ->blueprint($resource->blueprint())
            ->setColumnPreferenceKey("runway.{$resource->handle()}.columns");
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

        if ($data instanceof Model) {
            $data = Arr::wrap($data);
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

                $model = $resource->model()->firstWhere($resource->primaryKey(), $item);
            } else {
                $model = $item;
            }

            if (! $model) {
                return null;
            }

            return [
                'id' => $model->{$resource->primaryKey()},
                'title' => $fieldtype->preProcessIndex($model->{$column}),
                'edit_url' => $model->runwayEditUrl(),
            ];
        });
    }

    public function augment($values)
    {
        $resource = Runway::findResource($this->config('resource'));

        $results = $this->getAugmentableModels($resource, $values)
            ->map(function ($model) use ($resource) {
                return Blink::once("Runway::FieldtypeAugment::{$resource->handle()}_{$model->{$resource->primaryKey()}}", function () use ($model) {
                    return $model->toAugmentedArray();
                });
            });

        return $this->config('max_items') === 1
            ? $results->first()
            : $results->toArray();
    }

    public function shallowAugment($values)
    {
        $resource = Runway::findResource($this->config('resource'));

        $results = $this->getAugmentableModels($resource, $values)
            ->map(function ($model) use ($resource) {
                return Blink::once("Runway::FieldtypeShallowAugment::{$resource->handle()}_{$model->{$resource->primaryKey()}}", function () use ($model) {
                    return $model->toShallowAugmentedArray();
                });
            });

        return $this->config('max_items') === 1
            ? $results->first()
            : $results->toArray();
    }

    protected function getAugmentableModels(Resource $resource, $values): Collection
    {
        return collect($values instanceof Collection ? $values : Arr::wrap($values))
            ->map(function ($model) use ($resource) {
                if (! $model instanceof Model) {
                    $eagerLoadingRelationships = collect($this->config('with') ?? [])->join(',');

                    return Blink::once("Runway::Model::{$this->config('resource')}_{$model}}::{$eagerLoadingRelationships}", function () use ($resource, $model) {
                        return $resource->model()
                            ->when(
                                $this->config('with'),
                                fn ($query) => $query->with(Arr::wrap($this->config('with'))),
                                fn ($query) => $query->with($resource->eagerLoadingRelationships())
                            )
                            ->firstWhere($resource->primaryKey(), $model);
                    });
                }

                return $model;
            })
            ->filter();
    }

    protected function getColumns()
    {
        $resource = Runway::findResource($this->config('resource'));

        return $resource->blueprint()->columns()
            ->when($resource->hasPublishStates(), function ($collection) {
                $collection->put('status', Column::make('status')
                    ->listable(true)
                    ->visible(true)
                    ->defaultVisibility(true)
                    ->sortable(false));
            })
            ->setPreferred("runway.{$resource->handle()}.columns")
            ->rejectUnlisted()
            ->values();
    }

    protected function toItemArray($id)
    {
        $resource = Runway::findResource($this->config('resource'));
        $model = $id instanceof Model ? $id : $resource->model()->firstWhere($resource->primaryKey(), $id);

        if (! $model) {
            return $this->invalidItemArray($id);
        }

        return (new FieldtypeModel($model, $this))->resolve()['data'];
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

    public function filter()
    {
        return new Models($this);
    }

    protected function statusIcons()
    {
        $resource = Runway::findResource($this->config('resource'));

        return $resource->hasPublishStates();
    }

    private function applySearch(Resource $resource, Builder $query, string $searchQuery): Builder|BaseStatamicBuilder
    {
        if (! $searchQuery) {
            return $query;
        }

        if ($resource->hasSearchIndex() && ($index = $resource->searchIndex())) {
            return $index->ensureExists()->search($searchQuery);
        }

        return $query->runwaySearch($searchQuery);
    }
}
