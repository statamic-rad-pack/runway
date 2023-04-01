<?php

namespace DuncanMcClean\Runway\Routing\Traits;

use DuncanMcClean\Runway\Models\RunwayUri;
use DuncanMcClean\Runway\Routing\RoutingModel;
use DuncanMcClean\Runway\Runway;
use Illuminate\Support\Str;
use Statamic\Routing\Routable;
use Statamic\StaticCaching\Cacher;
use Statamic\Support\Arr;
use Statamic\View\Antlers\Parser;

trait RunwayRoutes
{
    protected $routingModel;

    use Routable {
        uri as routableUri;
    }

    public function routingModel()
    {
        $this->routingModel = new RoutingModel($this);

        return $this->routingModel;
    }

    public function route()
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

    public function uri()
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function runwayUri()
    {
        return $this->morphOne(RunwayUri::class, 'model');
    }

    public static function bootRunwayRoutes()
    {
        static::saved(function ($model) {
            $resource = Runway::findResourceByModel($model);

            if (! $resource->hasRouting()) {
                return;
            }

            $uri = (new Parser())
                ->parse($resource->route(), $resource->augment($model))
                ->__toString();

            $uri = Str::start($uri, '/');

            if ($model->runwayUri()->exists()) {
                $model->runwayUri()->first()->update([
                    'uri' => $uri,
                ]);
            } else {
                $model->runwayUri()->create([
                    'uri' => $uri,
                ]);
            }

            app(Cacher::class)->invalidateUrl(
                $uri,
            );

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
