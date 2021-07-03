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
        static::$resources = collect(config('runway.resources'))
            ->mapWithKeys(function ($config, $model) {
                $handle = Str::lower(class_basename($model));

                $resource = (new Resource)
                    ->handle($handle)
                    ->model($model)
                    ->name($config['name'])
                    ->blueprint($config['blueprint']);

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

                if (isset($config['template'])) {
                    $resource->template($config['template']);
                }

                if (isset($config['layout'])) {
                    $resource->layout($config['layout']);
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

    public static function findResourceByModel(object $model): ?Resource
    {
        $resource = collect(static::$resources)->filter(function (Resource $resource) use ($model) {
            return get_class($resource->model()) === get_class($model);
        })->first();

        if (! $resource) {
            // TODO: replace with ResourceNotFound
            throw new ModelNotFound("Runway could not find [{$model}]. Please ensure its configured properly and you're using the correct model.");
        }

        return $resource;
    }

    public static function usesRouting(): bool
    {
        return static::allResources()
            ->filter
            ->hasRouting()
            ->count() >= 1;
    }
}
