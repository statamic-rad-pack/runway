<?php

namespace StatamicRadPack\Runway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use StatamicRadPack\Runway\Exceptions\ResourceNotFound;

class Runway
{
    protected static array $resources = [];

    public static function discoverResources(): self
    {
        static::$resources = collect(config('runway.resources'))
            ->mapWithKeys(function ($config, $model) {
                $config = collect($config);
                $handle = $config->get('handle', Str::snake(class_basename($model)));

                throw_if(
                    ! in_array(Traits\HasRunwayResource::class, class_uses_recursive($model)),
                    new \Exception(__('The HasRunwayResource trait is missing from the [:model] model.', ['model' => $model]))
                );

                $resource = new Resource(
                    handle: $handle,
                    model: $model instanceof Model ? $model : new $model(),
                    name: $config['name'] ?? Str::title($handle),
                    config: $config,
                );

                return [$handle => $resource];
            })
            ->filter()
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
        return static::allResources()->filter->hasRouting()->count() >= 1;
    }
}
