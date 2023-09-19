<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Data\AugmentedModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Fields\Blueprint;
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
        protected array $config = []
    ) {
        if (isset($config['cp_icon'])) {
            $this->cpIcon($config['cp_icon']);
        }

        if (isset($config['hidden'])) {
            $this->hidden($config['hidden']);
        }

        if (isset($config['route'])) {
            $this->route($config['route']);
        }

        if (isset($config['template'])) {
            $this->template($config['template']);
        }

        if (isset($config['layout'])) {
            $this->layout($config['layout']);
        }

        if (isset($config['graphql'])) {
            $this->graphqlEnabled($config['graphql']);
        }

        if (isset($config['read_only'])) {
            $this->readOnly($config['read_only']);
        }

        if (isset($config['with'])) {
            $this->eagerLoadingRelations($config['with']);
        }

        if (isset($config['order_by'])) {
            $this->orderBy($config['order_by']);
        }

        if (isset($config['order_by_direction'])) {
            $this->orderByDirection($config['order_by_direction']);
        }

        if (isset($config['title_field'])) {
            $this->titleField($config['title_field']);
        }
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
        return $this->config()->get('singular') ?? Str::singular($this->name);
    }

    public function plural(): string
    {
        return $this->config()->get('plural') ?? Str::plural($this->name);
    }

    public function blueprint()
    {
        return $this->blueprint;
    }

    public function config(): Collection
    {
        return collect($this->config);
    }

    public function cpIcon($cpIcon = null)
    {
        return $this->fluentlyGetOrSet('cpIcon')
            ->getter(function ($value) {
                if (! $value) {
                    return file_get_contents(__DIR__.'/../resources/svg/database.svg');
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function hidden($hidden = null)
    {
        return $this->fluentlyGetOrSet('hidden')
            ->setter(function ($value) {
                if (! $value) {
                    return false;
                }

                return $value;
            })
            ->getter(function ($value) {
                if (! $this->blueprint()) {
                    return true;
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')
            ->args(func_get_args());
    }

    public function template($template = null)
    {
        return $this->fluentlyGetOrSet('template')
            ->getter(fn ($value) => $value ?? 'default')
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this->fluentlyGetOrSet('layout')
            ->getter(fn ($value) => $value ?? 'layout')
            ->args(func_get_args());
    }

    public function graphqlEnabled($graphqlEnabled = null)
    {
        return $this->fluentlyGetOrSet('graphqlEnabled')
            ->getter(fn ($graphqlEnabled) => $graphqlEnabled ?? false)
            ->args(func_get_args());
    }

    public function readOnly($readOnly = null)
    {
        return $this->fluentlyGetOrSet('readOnly')
            ->args(func_get_args());
    }

    public function orderBy($orderBy = null)
    {
        return $this->fluentlyGetOrSet('orderBy')
            ->getter(function ($value) {
                if (! $value) {
                    return $this->primaryKey();
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function orderByDirection($orderByDirection = null)
    {
        return $this->fluentlyGetOrSet('orderByDirection')
            ->getter(function ($value) {
                if (! $value) {
                    return 'asc';
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function titleField($field = null)
    {
        return $this
            ->fluentlyGetOrSet('titleField')
            ->getter(fn ($field) => $field ?? $this->listableColumns()[0])
            ->args(func_get_args());
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
                    return $this->blueprint()->fields()->items()
                        ->filter(function ($field) {
                            $type = $field['field']['type'] ?? null;

                            return $type === 'belongs_to'
                                || $type === 'has_many';
                        })
                        ->mapWithKeys(function ($field) {
                            $relationName = $field['handle'];

                            // If the field has a `relationship_name` config key, use that instead.
                            // Sometimes a column name will be different to the relationship name
                            // (the method on the model) so our magic won't be able to figure out what's what.
                            // Eg. standard_parent_id -> parent
                            if (isset($field['field']['relationship_name'])) {
                                $relationName = $field['field']['relationship_name'];
                            }

                            // If field handle is `author_id`, strip off the `_id`
                            if (str_contains($relationName, '_id')) {
                                $relationName = Str::replaceLast('_id', '', $relationName);
                            }

                            // If field handle contains an underscore, convert the name to camel case
                            if (str_contains($relationName, '_')) {
                                $relationName = Str::camel($relationName);
                            }

                            return [
                                $field['handle'] => $relationName,
                            ];
                        })
                        ->merge(['runwayUri'])
                        ->filter(fn ($relationName) => method_exists($this->model(), $relationName));
                }

                return collect($eagerLoadingRelations);
            })
            ->args(func_get_args());
    }

    /**
     * Returns an array of column handles that are marked as listable.
     */
    public function listableColumns(): array
    {
        return $this->blueprint()->fields()->items()
            ->reject(function ($field) {
                return isset($field['import'])
                    || (isset($field['field']['listable']) && $field['field']['listable'] === 'hidden')
                    || (isset($field['field']['listable']) && $field['field']['listable'] === false);
            })
            ->pluck('handle')
            ->toArray();
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

    public function augment(Model $model): array
    {
        return AugmentedModel::augment($model, $this->blueprint());
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
