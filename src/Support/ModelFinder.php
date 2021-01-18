<?php

namespace DoubleThreeDigital\Runway\Support;

use DoubleThreeDigital\Runway\Exceptions\ModelNotFound;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Facades\Blueprint;

class ModelFinder
{
    protected static array $models;

    public static function bootModels()
    {
        static::$models = collect(config('runway.models'))
            ->map(function ($config, $model) {
                $blueprint = is_string($config['blueprint'])
                    ? Blueprint::find($config['blueprint'])
                    : Blueprint::make()->setContents($config['blueprint']);

                $eloquentModel = (new $model());

                return [
                    '_handle'           => Str::lower(class_basename($model)),
                    'model'             => $model,
                    'name'              => $config['name'],
                    'singular'          => Str::singular($config['name']),
                    'blueprint'         => $blueprint,
                    'listing_columns'   => $config['listing']['columns'],
                    'listing_sort'      => $config['listing']['sort'],
                    'primary_key'       => $eloquentModel->getKeyName(),
                    'route_key'         => $eloquentModel->getRouteKey() ?? 'id',
                    'model_table'       => $modelTable = (new $model())->getTable(),
                    'schema_columns'    => Schema::getColumnListing($modelTable),
                    'cp_icon'           => isset($config['listing']['cp_icon'])
                        ? $config['listing']['cp_icon']
                        : file_get_contents(__DIR__.'/../../resources/svg/database.svg'),
                ];
            })
            ->toArray();
    }

    public static function all(): ?Collection
    {
        return collect(static::$models);
    }

    public static function find(string $modelHandle): ?array
    {
        $model = collect(static::$models)
            ->where('_handle', $modelHandle)
            ->first();

        if (! $model) {
            throw new ModelNotFound("Runway could not find the model ({$modelHandle}). Please make sure it's configured correctly and you're using the correct handle.");
        }

        return $model;
    }
}
