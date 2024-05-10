<?php

namespace StatamicRadPack\Runway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Revisions\Revisable;
use Statamic\Statamic;
use StatamicRadPack\Runway\Fieldtypes\BelongsToFieldtype;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;

class Resource
{
    use Revisable;

    public function __construct(
        protected string $handle,
        protected Model $model,
        protected string $name,
        protected Collection $config
    ) {
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function model(): Model
    {
        return $this->model;
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
            $blueprint = Blueprint::make($this->handle)->setNamespace('runway')->save();
        }

        return $blueprint;
    }

    public function config(): Collection
    {
        return $this->config;
    }

    public function cpIcon(): string
    {
        if (! $this->config->has('cp_icon')) {
            return file_get_contents(__DIR__.'/../resources/svg/database.svg');
        }

        return $this->config->get('cp_icon');
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
            ->reject(function ($handle) {
                $field = $this->blueprint()->field($handle);

                return $field->fieldtype()->indexComponent() === 'relationship' || $field->type() === 'section';
            })
            ->first();
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
                    $eloquentRelationship = $field->get('relationship_name');
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
            && in_array(\StatamicRadPack\Runway\Routing\Traits\RunwayRoutes::class, class_uses($this->model()));
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
        return Schema::getColumnListing($this->databaseTable());
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'model' => $this->model(),
            'name' => $this->name(),
            'blueprint' => $this->blueprint(),
            'cp_icon' => $this->cpIcon(),
            'hidden' => $this->hidden(),
            'route' => $this->route(),
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

    public function revisionsEnabled(): bool
    {
        return $this->config->get('revisions', false);
    }

    protected function revisionKey()
    {
        return vsprintf('resources/%s/%s', [
            $this->name(),
            $this->model->id(),
        ]);
    }

    protected function revisionAttributes()
    {
        return [
            'id' => $this->id(),
            'published' => $this->published(),
            'data' => $this->model->toArray(),
        ];
    }

    public function makeFromRevision($revision)
    {
        $entry = clone $this;

        if (! $revision) {
            return $entry;
        }

        $attrs = $revision->attributes();

        $model = $attrs['class']::make([$attrs['data']]);


        return $entry;
    }
}
