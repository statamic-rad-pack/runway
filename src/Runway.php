<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Exceptions\ModelNotFound;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Runway
{
    protected static array $resources = [];

    public static function discoverResources()
    {
        static::$resources = collect(config('runway.models'))
            ->mapWithKeys(function ($config, $model) {
                $handle = Str::lower(class_basename($model));

                $resource = (new Resource)
                    ->handle($handle)
                    ->model($model)
                    ->name($config['name'])
                    ->blueprint($config['blueprint'])
                    ->listingColumns($config['listing']['columns'])
                    ->listingSort($config['listing']['sort']);

                if (isset($config['listing']['buttons'])) {
                    $resource->listingButtons($config['listing']['blueprints']);
                }

                if (isset($config['listing']['cp_icon'])) {
                    $resource->cpIcon($config['listing']['cp_icon']);
                }

                if (isset($config['hidden'])) {
                    $resource->hidden($config['hidden']);
                }

                if (isset($config['route'])) {
                    $resource->route($config['route']);
                }

                return [$handle => $resource];
            })
            ->toArray();

        return new static;
    }

    public static function allResources(): Collection
    {
        return collect(static::$resources);
    }

    public static function findResource(string $resourceHandle): ?Resource
    {
        $resource = collect(static::$resources)->get($resourceHandle);

        if (! $resource) {
            // TODO: replace with ResourceNotFound
            throw new ModelNotFound("Runway could not find [{$resourceHandle}]. Please ensure its configured properly and you're using the correct handle.");
        }

        return $resource;
    }
}
