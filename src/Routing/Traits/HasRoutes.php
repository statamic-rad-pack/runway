<?php

namespace DoubleThreeDigital\Runway\Routing\Traits;

use DoubleThreeDigital\Runway\AugmentedRecord;
use DoubleThreeDigital\Runway\Models\RunwayUri;
use DoubleThreeDigital\Runway\ResourceResponse;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Arr;
use Statamic\Routing\Routable;
use Statamic\View\Antlers\Parser;

trait HasRoutes
{
    use Routable {
        uri as routableUri;
    }

    public function route()
    {
        return '/'.$this->slug();
    }

    public function routeData()
    {
        return [];
    }

    public function uri()
    {
        return $this->routableUri();
    }

    public function toResponse($request)
    {
        return (new ResourceResponse($this))->toResponse($request);
    }

    public function template()
    {
        return 'default';
    }

    public function layout()
    {
        return 'layout';
    }

    public function id()
    {
        return $this->getKey();
    }

    public function getRouteKey()
    {
        return $this->getAttributeValue($this->getRouteKeyName());
    }

    public function asResource()
    {
        return Runway::findResourceByModel($this);
    }

    public function toAugmentedArray()
    {
        return AugmentedRecord::augment($this, $this->asResource()->blueprint());
    }

    public function runwayUri()
    {
        return $this->morphOne(RunwayUri::class, 'model');
    }

    public static function bootHasRoutes()
    {
        static::saved(function ($model) {
            $resource = Runway::findResourceByModel($model);
            $augmentedModel = AugmentedRecord::augment($model, $resource->blueprint());

            if (! $resource->route()) {
                return;
            }

            $uri = (new Parser)
                ->parse($resource->route(), $augmentedModel)
                ->__toString();

            if ($model->runwayUri()->exists()) {
                $model->runwayUri()->first()->update([
                    'uri' => $uri,
                ]);
            } else {
                $model->runwayUri()->create([
                    'uri' => $uri,
                ]);
            }
        });

        static::deleting(function ($model) {
            $model->runwayUri()->delete();
        });
    }
}
