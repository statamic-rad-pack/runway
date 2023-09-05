<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Exceptions\ResourceNotFound;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Fields\Blueprint;

class Runway
{
    protected static array $resources = [];

    public static function discoverResources(): self
    {
        static::$resources = collect(config('runway.resources'))
            ->mapWithKeys(function ($config, $model) {
                $blueprint = null;
                $handle = Str::lower(class_basename($model));

                if (isset($config['handle'])) {
                    $handle = $config['handle'];
                }

                if (! in_array(Traits\HasRunwayResource::class, class_uses_recursive($model))) {
                    throw new \Exception(__('The HasRunwayResource trait is missing from the [:model] model.', ['model' => $model]));
                }

                if (! isset($config['blueprint'])) {
                    throw new \Exception(__('The [:model] model is missing a blueprint.', ['model' => $model]));
                }

                if (is_string($config['blueprint'])) {
                    $blueprint = Blueprint::find($config['blueprint']);
                }

                if (is_array($config['blueprint'])) {
                    $blueprint = Blueprint::make()
                        ->setHandle($handle)
                        ->setContents($config['blueprint']);
                }

                $resource = new Resource(
                    handle: $handle,
                    model: $model instanceof Model ? $model : new $model(),
                    name: $config['name'] ?? Str::title($handle),
                    blueprint: $blueprint,
                    config: $config,
                );

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
        $resource = collect(static::$resources)->filter(fn (Resource $resource) => $model::class === $resource->model()::class)->first();

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
