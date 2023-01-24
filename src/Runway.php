<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Exceptions\ResourceNotFound;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Runway
{
    protected static array $resources = [];

    public static function discoverResources(): self
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

                if (isset($config['title_field'])) {
                    $resource->titleField($config['title_field']);
                }

                if (isset($config['blueprint'])) {
                    $resource->blueprint($config['blueprint']);
                }

                if (isset($config['cp_icon'])) {
                    $resource->cpIcon($config['cp_icon']);
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

                if (isset($config['read_only'])) {
                    $resource->readOnly($config['read_only']);
                }

                if (isset($config['with'])) {
                    $resource->eagerLoadingRelations($config['with']);
                }

                if (isset($config['order_by'])) {
                    $resource->orderBy($config['order_by']);
                }

                if (isset($config['order_by_direction'])) {
                    $resource->orderByDirection($config['order_by_direction']);
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
        $resource = collect(static::$resources)->filter(fn (Resource $resource) => $resource->model()::class === $model::class)->first();

        if (! $resource) {
            throw new ResourceNotFound($model::class);
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
