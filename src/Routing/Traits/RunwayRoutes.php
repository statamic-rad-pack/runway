<?php

namespace DoubleThreeDigital\Runway\Routing\Traits;

use DoubleThreeDigital\Runway\Models\RunwayUri;
use DoubleThreeDigital\Runway\Routing\ResourceResponse;
use DoubleThreeDigital\Runway\Runway;
use Statamic\Routing\Routable;
use Statamic\View\Antlers\Parser;

trait RunwayRoutes
{
    use Routable {
        uri as routableUri;
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
        return [
            'id' => $this->{$this->getKeyName()},
        ];
    }

    public function uri()
    {
        return $this->routableUri();
    }

    public function toResponse($request)
    {
        return (new ResourceResponse($this))->toResponse($request);
    }

    public function template(): string
    {
        return Runway::findResourceByModel($this)->template();
    }

    public function layout(): string
    {
        return Runway::findResourceByModel($this)->layout();
    }

    public function id()
    {
        return $this->getKey();
    }

    public function getRouteKey()
    {
        return $this->getAttributeValue($this->getRouteKeyName());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function runwayUri()
    {
        return $this->morphOne(RunwayUri::class, 'model');
    }
    
    public function setSupplement($key, $value)
    {
        $this->$key = $value;
    }

    public static function bootRunwayRoutes()
    {
        static::saved(function ($model) {
            $resource = Runway::findResourceByModel($model);

            if (! $resource->hasRouting()) {
                return;
            }

            $uri = (new Parser)
                ->parse($resource->route(), $resource->augment($model))
                ->__toString();

            if ($model->runwayUri()->exists()) {
                $model->runwayUri()->first()->update([
                    'uri' => $uri,
                ]);
            } else {
                // dd($uri);

                $model->runwayUri()->create([
                    'uri' => $uri,
                ]);
            }
        });

        static::deleting(function ($model) {
            if ($model->runwayUri) {
                $model->runwayUri()->delete();
            }
        });
    }
}
