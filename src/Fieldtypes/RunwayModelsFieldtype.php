<?php

namespace StatamicRadPack\Runway\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Data\Augmentable;
use Statamic\CP\Column;
use Statamic\Facades\Blink;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\Search;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Relationship;
use Statamic\Query\OrderBy;
use Statamic\Query\Scopes\Filter;
use Statamic\Search\QueryBuilder as SearchQueryBuilder;
use StatamicRadPack\Runway\Http\Resources\CP\FieldtypeModel;
use StatamicRadPack\Runway\Http\Resources\CP\FieldtypeModels;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Scopes\Fields\Models;

class RunwayModelsFieldtype extends Relationship
{
    protected $component = 'runway-models';
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $keywords = ['model', 'eloquent'];
    protected $formComponent = 'runway-publish-form';
    protected $itemComponent = 'runway-model-item';

    protected $formComponentProps = [
        'initialActions' => 'actions',
        'method' => 'method',
        'resource' => 'resource',
        'resourceHasRoutes' => 'resourceHasRoutes',
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialStatus' => 'status',
        'initialBlueprint' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'initialPermalink' => 'permalink',
        'initialIsWorkingCopy' => 'hasWorkingCopy',
        'revisionsEnabled' => 'revisionsEnabled',
        'initialReadOnly' => 'readOnly',
        'canEditBlueprint' => 'canEditBlueprint',
        'canManagePublishState' => 'canManagePublishState',
    ];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'resource' => [
                        'display' => __('Resource'),
                        'instructions' => __('runway::fieldtypes.relationship.config.resource'),
                        'type' => 'select',
                        'options' => Runway::allResources()->map->name()->all(),
                        'validate' => ['required', 'in:'.Runway::allResources()->keys()->implode(',')],
                        'width' => 50,
                    ],
                    'reorderable' => [
                        'display' => __('Reorderable?'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => 50,
                    ],
                    'query_scopes' => [
                        'display' => __('Query Scopes'),
                        'instructions' => __('runway::fieldtypes.relationship.config.query_scopes'),
                        'type' => 'taggable',
                        'options' => Scope::all()->reject(fn ($scope) => $scope instanceof Filter)->map->handle()->values()->all(),
                        'width' => 50,
                    ],
                    'search_index' => [
                        'display' => __('Search Index'),
                        'instructions' => __('runway::fieldtypes.relationship.config.search_index'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                    'title_format' => [
                        'display' => __('Title Format'),
                        'instructions' => __('runway::fieldtypes.relationship.config.title_format'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                    'with' => [
                        'display' => __('Eager Loaded Relationships'),
                        'instructions' => __('runway::fieldtypes.relationship.config.with'),
                        'type' => 'list',
                        'width' => 50,
                    ],
                    'create' => [
                        'display' => __('Allow Creating'),
                        'instructions' => __('runway::fieldtypes.relationship.config.create'),
                        'type' => 'toggle',
                        'default' => true,
                        'if' => ['mode' => 'default'],
                    ],
                ],
            ],
            ...parent::configFieldItems(),
        ];
    }

    public function icon(): string
    {
        return File::get(__DIR__.'/../../resources/svg/database.svg');
    }

    public function preProcess($data): array
    {
        return $this->ids($data);
    }

    private function ids(mixed $data): array
    {
        return collect($data instanceof Collection ? $data : Arr::wrap($data))
            ->map(fn ($value) => $value instanceof Model ? $value->getKey() : $value)
            ->filter(fn ($id) => is_int($id) || (is_string($id) && $id !== ''))
            ->uniqueStrict(fn ($id) => (string) $id)->values()->all();
    }

    public function process($data): int|string|array
    {
        $ids = $this->ids($data);

        return $this->config('max_items') === 1 && $ids !== [] ? $ids[0] : $ids;
    }

    public function toQueryableValue($value): int|string|array
    {
        return $this->process($value);
    }

    public function rules(): array
    {
        return [...parent::rules(), function ($attribute, $value, $fail) {
            $ids = $this->ids($value);

            if (! is_array($value) || collect($value)->contains(fn ($id) => ! is_int($id) && (! is_string($id) || $id === ''))) {
                $fail(__('runway::validation.models'));

                return;
            }

            $existing = array_map('strval', $this->savedIds());
            $models = $this->selectionQuery()->whereKey($ids)->get()->keyBy(fn ($model) => (string) $model->getKey());

            foreach ($ids as $id) {
                if (in_array((string) $id, $existing, true)) {
                    continue;
                }

                if (! ($model = $models->get((string) $id)) || ! User::current()?->can('view', [$this->resource(), $model])) {
                    $fail(__('runway::validation.models'));

                    return;
                }
            }
        }];
    }

    private function savedIds(): array
    {
        $field = $this->field;
        $children = [];

        while ($container = $field->parentField()) {
            array_unshift($children, $field);
            $field = $container;
        }

        $values = collect([$this->savedValue($field->handle())]);

        foreach ($children as $child) {
            $values = $values->flatMap(fn ($rows) => $this->savedRowValues($rows, $child));
        }

        return $this->ids($values->flatten(1));
    }

    private function savedValue(string $handle): mixed
    {
        $parent = $this->field->parent();

        if ($parent && method_exists($parent, 'fresh')) {
            $parent = $parent->fresh();

            if (! $parent) {
                return null;
            }

            if (method_exists($parent, 'fromWorkingCopy')) {
                $parent = $parent->fromWorkingCopy();
            }

            if ($parent instanceof Model) {
                return method_exists($parent, 'getFieldValue')
                    ? $parent->getFieldValue($handle)
                    : $parent->getAttribute($handle);
            }

            return $parent->get($handle);
        }

        if ($parent instanceof Asset) {
            return $parent->getOriginal('data.'.$handle);
        }

        return null;
    }

    private function savedRowValues(mixed $rows, Field $child): array
    {
        $container = $child->parentField();
        $bard = $container->type() === 'bard';
        $idPath = $bard ? 'attrs.id' : '_id';
        $typePath = $bard ? 'attrs.values.type' : 'type';
        $savedIdPath = $bard ? 'attrs.id' : RowId::handle();
        $submittedRow = data_get($container->value(), $child->parentIndex());
        $rowId = data_get($submittedRow, $idPath);
        $savedRows = collect($rows);

        if ($container->type() !== 'grid') {
            $savedRows = $savedRows->filter(fn ($row) => data_get($row, $typePath) === data_get($submittedRow, $typePath));
        }

        $matches = $savedRows->filter(fn ($row) => $rowId !== null && data_get($row, $savedIdPath) === $rowId);

        if ($matches->isEmpty()) {
            $matches = $savedRows->filter(fn ($row) => data_get($row, $savedIdPath) === null);
        }

        return $matches->map(fn ($row) => data_get($row, ($bard ? 'attrs.values.' : '').$child->handle()))->values()->all();
    }

    private function selectionQuery(array $params = []): Builder
    {
        $query = $this->resource()->newEloquentQuery();
        $query->when($query->hasNamedScope('runwayListing'), fn ($query) => $query->runwayListing());
        $this->applyIndexQueryScopes($query, $params);

        return $query;
    }

    public function getIndexItems($request): Collection|Paginator
    {
        if (! User::current()?->can('view', $this->resource())) {
            return collect();
        }

        $query = $this->selectionQuery($request->all());

        if ($search = $request->input('search')) {
            $index = $this->config('search_index') ? Search::in($this->config('search_index')) : $this->resource()->searchIndex();
            $query = $index ? $index->ensureExists()->search($search) : $query->runwaySearch($search);

            if ($query instanceof SearchQueryBuilder) {
                $references = $this->selectionQuery($request->all())->toBase()->pluck($this->resource()->qualifiedPrimaryKey())
                    ->map(fn ($id) => "runway::{$this->resource()->handle()}::{$id}")->all();
                $query->withoutData()->whereIn('reference', $references);
            }
        }

        $column = OrderBy::column($request->input('sort'));

        if ($column) {
            $column = $column === 'title' ? $this->resource()->titleField() : $column;
            $column = $this->resource()->model()->getFieldColumn($column);
            $query instanceof Builder ? $query->reorder($column, $request->input('order', 'asc')) : $query->orderBy($column, $request->input('order', 'asc'));
        } elseif ($query instanceof Builder && ! $query->getQuery()->orders) {
            $query->runwayOrderBy($this->resource()->orderBy(), $this->resource()->orderByDirection());
        }

        $results = $request->boolean('paginate', true) ? $query->paginate($request->integer('perPage', 15)) : $query->get();
        $items = $results instanceof Paginator ? $results->getCollection() : $results;

        if ($query instanceof SearchQueryBuilder) {
            $ids = $items->map(fn ($item) => Str::after($item->get('reference'), "runway::{$this->resource()->handle()}::"));
            $models = $this->resource()->newEloquentQuery()->whereKey($ids->all())->get()->keyBy(fn ($model) => (string) $model->getKey());
            $items = $ids->map(fn ($id) => $models->get((string) $id))->filter();
        }

        $items = $items->filter(fn ($model) => User::current()->can('view', [$this->resource(), $model]))->values();

        return $results instanceof Paginator ? $results->setCollection($items) : $items;
    }

    public function getResourceCollection($request, $items): FieldtypeModels
    {
        return (new FieldtypeModels($items, $this))
            ->runwayResource($this->resource())
            ->blueprint($this->resource()->blueprint())
            ->setColumnPreferenceKey("runway.{$this->resource()->handle()}.columns");
    }

    protected function getColumns(): Collection
    {
        return $this->resource()->blueprint()->columns()
            ->when($this->resource()->hasPublishStates(), fn ($columns) => $columns->put('status', Column::make('status')->listable(true)->visible(true)->defaultVisibility(true)->sortable(false)))
            ->setPreferred("runway.{$this->resource()->handle()}.columns")->rejectUnlisted()->values();
    }

    protected function toItemArray($id): array
    {
        $model = $this->selectionQuery()->whereKey($id)->first();

        if (! $model || ! User::current()?->can('view', [$this->resource(), $model])) {
            return $this->invalidItemArray($id);
        }

        return (new FieldtypeModel($model, $this))->resolve()['data'];
    }

    protected function canCreate(): bool
    {
        return parent::canCreate() && User::current()?->can('create', $this->resource());
    }

    protected function canEdit(): bool
    {
        return parent::canEdit() && User::current()?->can('edit', $this->resource());
    }

    protected function getCreatables(): array
    {
        return [[
            'title' => $this->resource()->singular(),
            'url' => cp_route('runway.create', ['resource' => $this->resource()->handle()]),
        ]];
    }

    protected function statusIcons(): bool
    {
        return $this->resource()->hasPublishStates();
    }

    public function preProcessIndex($data): Collection
    {
        return $this->getItemData($this->ids($data));
    }

    public function augment($values): ?array
    {
        $models = $this->augmentableModels($values)->map->toDeferredAugmentedArray();

        return $this->config('max_items') === 1 ? $models->first() : $models->all();
    }

    public function shallowAugment($values): ?array
    {
        $models = $this->augmentableModels($values)->map->toShallowAugmentedArray();

        return $this->config('max_items') === 1 ? $models->first() : $models->all();
    }

    private function augmentableModels(mixed $values): Collection
    {
        $resource = $this->resource();
        $with = $this->config('with') ?? $resource->eagerLoadingRelationships();
        $key = fn ($id) => 'RunwayModels::'.$resource->handle().'::'.json_encode([(string) $id, $with]);
        $values = collect($values instanceof Collection ? $values : Arr::wrap($values));

        $values->filter(fn ($value) => $value instanceof Model)->each(fn ($model) => Blink::put($key($model->getKey()), $model));

        $ids = collect($this->ids($values))->merge($this->siblingIds())->uniqueStrict(fn ($id) => (string) $id)
            ->reject(fn ($id) => Blink::has($key($id)))->values();

        if ($ids->isNotEmpty()) {
            $models = $resource->newEloquentQuery()->with(Arr::wrap($with))->whereKey($ids->all())->get()->keyBy(fn ($model) => (string) $model->getKey());
            $ids->each(fn ($id) => Blink::put($key($id), $models->get((string) $id)));
        }

        return collect($this->ids($values))->map(fn ($id) => Blink::get($key($id)))->filter()
            ->when($resource->hasPublishStates(), fn ($models) => $models->filter->published())->values();
    }

    private function siblingIds(): array
    {
        $field = $this->field;
        $parent = $field?->parent();

        if (! $field?->parentField() || ! $parent instanceof Augmentable) {
            return [];
        }

        [$root] = $field->handlePath();
        $ids = function ($value) use (&$ids, $field) {
            return collect($value)->flatMap(fn ($item, $key) => match (true) {
                $key === $field->handle() => $this->ids($item),
                is_array($item) => $ids($item),
                default => [],
            });
        };

        return $ids($parent->augmentedValue($root)->raw())->uniqueStrict(fn ($id) => (string) $id)->values()->all();
    }

    public function toGqlType(): array
    {
        $type = GraphQL::type("runway_graphql_types_{$this->resource()->handle()}");

        return [
            'type' => $this->config('max_items') === 1 ? $type : GraphQL::listOf($type),
            'resolve' => function ($item) {
                $handle = $this->field->handle();
                $values = match (true) {
                    $item instanceof Model => method_exists($item, 'getFieldValue') ? $item->getFieldValue($handle) : $item->getAttribute($handle),
                    $item instanceof Values => $item->raw($handle),
                    $item instanceof Asset => $item->get($handle),
                    $item instanceof Collection => $item->get($handle),
                    is_array($item) => $item[$handle] ?? null,
                    default => $item->value($handle),
                };

                if ($item instanceof Collection) {
                    $values = collect($this->config('max_items') === 1 ? [$values] : $values)
                        ->map(fn ($model) => data_get($model, $this->resource()->primaryKey()))
                        ->map(fn ($id) => $id instanceof Value ? $id->raw() : $id)->all();
                }

                $models = $this->augmentableModels($values instanceof Value ? $values->raw() : $values);

                return $this->config('max_items') === 1 ? $models->first() : $models->all();
            },
        ];
    }

    public function filter(): Models
    {
        return new Models($this);
    }

    private function resource(): Resource
    {
        return Runway::findResource($this->config('resource'));
    }
}
