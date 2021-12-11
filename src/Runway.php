<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Exceptions\ResourceNotFound;
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

                if (isset($config['handle'])) {
                    $handle = $config['handle'];
                }

                $resource = (new Resource())
                    ->handle($handle)
                    ->model($model);

                if (isset($config['name'])) {
                    $resource->name($config['name']);
                } else {
                    $resource->name(Str::title($handle));
                }

                if (isset($config['blueprint'])) {
                    $resource->blueprint($config['blueprint']);
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

                if (isset($config['graphql'])) {
                    $resource->graphqlEnabled($config['graphql']);
                }

                return [$handle => $resource];
            })
            ->toArray();

        return new static();
    }

    public static function allResources(): Collection
    {
        return collect(static::$resources);
    }

    public static function findResource(string $resourceHandle): ?Resource
    {
        $resource = collect(static::$resources)->get($resourceHandle);

        if (! $resource) {
            throw new ResourceNotFound($resourceHandle);
        }

        return $resource;
    }

    public static function findResourceByModel(object $model): ?Resource
    {
        $resource = collect(static::$resources)->filter(function (Resource $resource) use ($model) {
            return get_class($resource->model()) === get_class($model);
        })->first();

        if (! $resource) {
            throw new ResourceNotFound(get_class($model));
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
