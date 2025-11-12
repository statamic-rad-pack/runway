<?php

namespace StatamicRadPack\Runway;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Facades\Blink;
use Statamic\Facades\Search;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Statamic;
use StatamicRadPack\Runway\Exceptions\PublishedColumnMissingException;
use StatamicRadPack\Runway\Fieldtypes\BelongsToFieldtype;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;

class Resource
{
    public function __construct(
        protected string $handle,
        protected Model $model,
        protected string $name,
        protected Collection $config
    ) {}

    public function handle(): string
    {
        return $this->handle;
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function newEloquentQuery(): Builder
    {
        return $this->model->newQuery()->runway();
    }

    public function newEloquentQueryBuilderWithEagerLoadedRelationships(): Builder
    {
        return $this
            ->newEloquentQuery()
            ->with(
                collect($this->eagerLoadingRelationships())
                    ->mapWithKeys(function (string $relationship): array {
                        if ($relationship === 'runwayUri') {
                            return [$relationship => fn ($query) => null];
                        }

                        return [$relationship => fn ($query) => $query->runway()];
                    })
                    ->all()
            );
    }

    public function name()
    {
        return $this->name;
    }

    public function singular(): string
    {
        return $this->config->get('singular') ?? Str::singular($this->name);
    }

    public function plural(): string
    {
        return $this->config->get('plural') ?? Str::plural($this->name);
    }

    public function blueprint(): Blueprint
    {
        $blueprint = Blueprint::find("runway::{$this->handle}");

        if (! $blueprint) {
            $blueprint = GenerateBlueprint::generate($this);
        }

        return $blueprint;
    }

    public function config(): Collection
    {
        return $this->config;
    }

    public function hidden(): bool
    {
        return $this->config->get('hidden', false);
    }

    public function route(): ?string
    {
        return $this->config->get('route');
    }

    public function template(): string
    {
        return $this->config->get('template', 'default');
    }

    public function layout(): string
    {
        return $this->config->get('layout', 'layout');
    }

    public function graphqlEnabled(): bool
    {
        if (! Statamic::pro()) {
            return false;
        }

        return $this->config->get('graphql', false);
    }

    public function hasVisibleBlueprint(): bool
    {
        return $this->blueprint()->hidden() === false;
    }

    public function readOnly(): bool
    {
        return $this->config->get('read_only', false);
    }

    public function orderBy(): string
    {
        return $this->config->get('order_by', $this->primaryKey());
    }

    public function orderByDirection(): string
    {
        return $this->config->get('order_by_direction', 'asc');
    }

    public function titleField()
    {
        if ($titleField = $this->config()->get('title_field')) {
            return $titleField;
        }

        return $this->listableColumns()
            ->filter(function ($handle) {
                $field = $this->blueprint()->field($handle);

                return in_array($field->type(), ['text', 'textarea', 'slug']);
            })
            ->first();
    }

    public function hasPublishStates(): bool
    {
        return is_string($published = $this->config->get('published'))
            || $published === true;
    }

    public function publishedColumn(): ?string
    {
        if (! $this->hasPublishStates()) {
            return null;
        }

        $column = is_string($this->config->get('published'))
            ? $this->config->get('published')
            : 'published';

        if (! in_array($column, $this->databaseColumns())) {
            throw new PublishedColumnMissingException($this->databaseTable(), $column);
        }

        return $column;
    }

    public function defaultPublishState(): ?bool
    {
        if (! $this->hasPublishStates()) {
            return null;
        }

        if ($this->revisionsEnabled()) {
            return false;
        }

        return $this->config->get('default_publish_state', 'published') === 'published';
    }

    public function nestedFieldPrefixes(): Collection
    {
        return collect($this->config->get('nested_field_prefixes'));
    }

    public function nestedFieldPrefix(string $field): ?string
    {
        return $this->nestedFieldPrefixes()
            ->reject(fn ($prefix) => $field === $prefix)
            ->filter(fn ($prefix) => Str::startsWith($field, $prefix))
            ->first();
    }

    public function ignoreCasts(string $field): bool
    {
        return in_array($field, $this->config->get('ignore_casts', []));
    }

    /**
     * Maps Eloquent relationships to their respective blueprint fields.
     */
    public function eloquentRelationships(): Collection
    {
        return $this->blueprint()->fields()->all()
            ->filter(function (Field $field) {
                return $field->fieldtype() instanceof BelongsToFieldtype
                    || $field->fieldtype() instanceof HasManyFieldtype;
            })
            ->mapWithKeys(function (Field $field) {
                $eloquentRelationship = $field->handle();

                // If the field has a `relationship_name` config key, use that instead.
                // Sometimes a column name will be different to the relationship name
                // (the method on the model) so our magic won't be able to figure out what's what.
                // Eg. standard_parent_id -> parent
                if ($field->get('relationship_name')) {
                    return [$field->handle() => $field->get('relationship_name')];
                }

                // If field handle is `author_id`, strip off the `_id`
                if (str_contains($eloquentRelationship, '_id')) {
                    $eloquentRelationship = Str::replaceLast('_id', '', $eloquentRelationship);
                }

                // If field handle contains an underscore, convert the name to camel case
                if (str_contains($eloquentRelationship, '_')) {
                    $eloquentRelationship = Str::camel($eloquentRelationship);
                }

                return [$field->handle() => $eloquentRelationship];
            })
            ->merge(['runwayUri'])
            ->filter(fn ($eloquentRelationship) => method_exists($this->model(), $eloquentRelationship));
    }

    /**
     * Defines the relationships which should be eager loaded when querying the model.
     */
    public function eagerLoadingRelationships(): array
    {
        if ($eagerLoadingRelationships = $this->config->get('with')) {
            return $eagerLoadingRelationships;
        }

        return $this->eloquentRelationships()->values()->toArray();
    }

    public function listableColumns(): Collection
    {
        return $this->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->isListable())
            ->map->handle()
            ->values();
    }

    public function hasRouting(): bool
    {
        return ! is_null($this->route())
            && in_array(\StatamicRadPack\Runway\Routing\Traits\RunwayRoutes::class, class_uses_recursive($this->model()));
    }

    public function primaryKey(): string
    {
        return $this->model()->getKeyName();
    }

    public function routeKey(): string
    {
        return $this->model()->getRouteKeyName() ?? 'id';
    }

    public function databaseTable(): string
    {
        return $this->model()->getTable();
    }

    public function databaseColumns(): array
    {
        return Blink::once('runway-database-columns-'.$this->databaseTable(), function () {
            return $this->model()->getConnection()->getSchemaBuilder()->getColumnListing($this->databaseTable());
        });
    }

    public function revisionsEnabled(): bool
    {
        if (! config('statamic.revisions.enabled') || ! Statamic::pro() || ! $this->hasPublishStates()) {
            return false;
        }

        return $this->config->get('revisions', false);
    }

    public function searchIndex()
    {
        if (! $index = $this->config->get('search_index', false)) {
            return;
        }

        return Search::index($index);
    }

    public function hasSearchIndex()
    {
        return $this->searchIndex() !== null;
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'model' => get_class($this->model()),
            'name' => $this->name(),
            'blueprint' => $this->blueprint(),
            'hidden' => $this->hidden(),
            'route' => $this->route(),
            'has_publish_states' => $this->hasPublishStates(),
            'published_column' => $this->publishedColumn(),
        ];
    }

    public function __get($name)
    {
        return $this->model()->{$name};
    }

    public function __call($name, $arguments)
    {
        return $this->model()->{$name}(...$arguments);
    }
}
