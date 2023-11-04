<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Statamic;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Resource
{
    use FluentlyGetsAndSets;

    protected $cpIcon;

    protected $hidden;

    protected $route;

    protected $template;

    protected $layout;

    protected $graphqlEnabled;

    protected $readOnly;

    protected $eagerLoadingRelations;

    protected $orderBy;

    protected $titleField;

    public function __construct(
        protected string $handle,
        protected Model $model,
        protected string $name,
        protected Blueprint $blueprint,
        protected Collection $config
    ) {
        $config->has('with') ? $this->eagerLoadingRelations($config->get('with')) : null;
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
        return $this->blueprint;
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

        return $this->config->has('cp_icon');
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
        return $this->config->get('title_field', $this->listableColumns()->first());
    }

    /**
     * Discovers any Eloquent relationships from fieldtypes in the resource's blueprint
     * OR those explicitly defined in the resource's config file.
     */
    public function eagerLoadingRelations($eagerLoadingRelations = null)
    {
        return $this->fluentlyGetOrSet('eagerLoadingRelations')
            ->getter(function ($eagerLoadingRelations) {
                if (! $eagerLoadingRelations) {
                    return $this->blueprint()->fields()->all()
                        ->filter(function (Field $field) {
                            return $field->fieldtype() instanceof BelongsToFieldtype
                                || $field->fieldtype() instanceof \DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
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

                return collect($eagerLoadingRelations);
            })
            ->args(func_get_args());
    }

    public function listableColumns(): Collection
    {
        return $this->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->isVisibleOnListing())
            ->map->handle()
            ->values();
    }

    public function hasRouting(): bool
    {
        return ! is_null($this->route())
            && in_array(\DoubleThreeDigital\Runway\Routing\Traits\RunwayRoutes::class, class_uses($this->model()));
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
}
