<?php

namespace DoubleThreeDigital\Runway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Illuminate\Support\Str;

class Resource
{
    use FluentlyGetsAndSets;

    protected $handle;
    protected $model;
    protected $name;
    protected $blueprint;
    protected $cpIcon;
    protected $hidden;
    protected $route;
    protected $template;
    protected $layout;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')
            ->args(func_get_args());
    }

    public function model($model = null)
    {
        return $this->fluentlyGetOrSet('model')
            ->setter(function ($value) {
                if (! $value instanceof Model) {
                    return (new $value());
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function name($name = null)
    {
        return $this->fluentlyGetOrSet('name')
            ->args(func_get_args());
    }

    public function singular(): string
    {
        return Str::singular($this->name);
    }

    public function plural(): string
    {
        return Str::plural($this->name);
    }

    public function blueprint()
    {
        return $this->fluentlyGetOrSet('blueprint')
            ->setter(function ($value) {
                if (is_string($value)) {
                    return \Statamic\Facades\Blueprint::find($value);
                }

                if (is_array($value)) {
                    return \Statamic\Facades\Blueprint::make()->setContents($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function listableColumns()
    {
        return $this->blueprint()->fields()->items()->reject(function ($field) {
            return isset($field['field']['listable']) && $field['field']['listable'] === 'hidden';
        })->pluck('handle')->toArray();
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
            ->getter(function ($value) {
                return $value ?? 'default';
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this->fluentlyGetOrSet('layout')
            ->getter(function ($value) {
                return $value ?? 'layout';
            })
            ->args(func_get_args());
    }

    public function hasRouting(): bool
    {
        return ! is_null($this->route())
            && in_array('DoubleThreeDigital\Runway\Routing\Traits\RunwayRoutes', class_uses($this->model()));
    }

    public function primaryKey()
    {
        return $this->model()->getKeyName();
    }

    public function routeKey()
    {
        return $this->model()->getRouteKeyName() ?? 'id';
    }

    public function databaseTable()
    {
        return $this->model()->getTable();
    }

    public function databaseColumns()
    {
        return Schema::getColumnListing($this->databaseTable());
    }

    public function toArray(): array
    {
        return [
            'handle'          => $this->handle(),
            'model'           => $this->model(),
            'name'            => $this->name(),
            'blueprint'       => $this->blueprint(),
            'cp_icon'         => $this->cpIcon(),
            'hidden'          => $this->hidden(),
            'route'           => $this->route(),
        ];
    }

    public function augment(Model $model)
    {
        return AugmentedRecord::augment($model, $this->blueprint());
    }

    public function __get($name)
    {
        return $this->model()->{$name};
    }

    public function __call($name, $arguments)
    {
        return $this->model()->{$name}($arguments);
    }
}
