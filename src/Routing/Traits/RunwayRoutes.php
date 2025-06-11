<?php

namespace StatamicRadPack\Runway\Routing\Traits;

use Illuminate\Support\Str;
use Statamic\Facades\Antlers;
use Statamic\StaticCaching\Cacher;
use Statamic\Support\Arr;
use StatamicRadPack\Runway\Routing\MorphOneWithStringKey;
use StatamicRadPack\Runway\Routing\Routable;
use StatamicRadPack\Runway\Routing\RoutingModel;
use StatamicRadPack\Runway\Routing\RunwayUri;
use StatamicRadPack\Runway\Runway;

trait RunwayRoutes
{
    protected $routingModel;

    use Routable {
        uri as routableUri;
    }

    public function routingModel(): RoutingModel
    {
        $this->routingModel = new RoutingModel($this);

        return $this->routingModel;
    }

    public function route(): ?string
    {
        if (! $this->runwayUri) {
            return null;
        }

        return $this->runwayUri->uri;
    }

    public function routeData()
    {
        return $this->routingModel()->routeData();
    }

    public function uri(): ?string
    {
        return $this->routingModel()->uri();
    }

    public function toResponse($request)
    {
        return $this->routingModel()->toResponse($request);
    }

    public function template(): string
    {
        return $this->routingModel()->template();
    }

    public function layout(): string
    {
        return $this->routingModel()->layout();
    }

    public function getRouteKey()
    {
        return $this->routingModel()->getRouteKey();
    }

    public function runwayUri(): MorphOneWithStringKey
    {
        return new MorphOneWithStringKey(RunwayUri::query(), $this, 'model_type', 'model_id', $this->getKeyName());
    }

    public static function bootRunwayRoutes()
    {
        static::saved(function ($model) {
            $resource = Runway::findResourceByModel($model);

            if (! $resource->hasRouting()) {
                return;
            }

            $uri = Antlers::parser()
                ->parse($resource->route(), $model->toAugmentedArray())
                ->__toString();

            $uri = Str::start($uri, '/');

            if ($model->runwayUri()->exists()) {
                $model->runwayUri()->first()->update(['uri' => $uri]);
            } else {
                $model->runwayUri()->create(['uri' => $uri]);
            }

            app(Cacher::class)->invalidateUrl($uri);

            app(Cacher::class)->invalidateUrls(
                Arr::get(config('statamic.static_caching.invalidation.rules'), "runway.{$resource->handle()}.urls")
            );
        });

        static::deleting(function ($model) {
            if ($model->runwayUri) {
                $model->runwayUri()->delete();
            }
        });
    }
}
