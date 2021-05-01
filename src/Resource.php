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
    protected $listingColumns;
    protected $listingSort;
    protected $listingButtons;
    protected $cpIcon;
    protected $hidden;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')
            ->args(func_get_args());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
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

    /**
     * @return Statamic\Fields\Blueprint
     */
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

    public function listingColumns()
    {
        return $this->fluentlyGetOrSet('listingColumns')
            ->args(func_get_args());
    }

    public function listingSort()
    {
        return $this->fluentlyGetOrSet('listingSort')
            ->args(func_get_args());
    }

    public function listingButtons($listingButtons = null)
    {
        return $this->fluentlyGetOrSet('listingButtons')
            ->getter(function ($value) {
                if (! $value) {
                    return [];
                }

                return $value;
            })
            ->args(func_get_args());
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
            ->args(func_get_args());
    }

    public function primaryKey()
    {
        return $this->model()->getKeyName();
    }

    public function routeKey()
    {
        return $this->model()->getRouteKey() ?? 'id';
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
            'listing_columns' => $this->listingColumns(),
            'listing_sort'    => $this->listingSort(),
            'listing_buttons' => $this->listingButtons(),
            'cp_icon'         => $this->cpIcon(),
            'hidden'          => $this->hidden(),
        ];
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
