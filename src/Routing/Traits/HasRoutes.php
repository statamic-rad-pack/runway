<?php

namespace DoubleThreeDigital\Runway\Routing\Traits;

use DoubleThreeDigital\Runway\ResourceResponse;
use Illuminate\Support\Arr;
use Statamic\Routing\Routable;

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
        return Arr::merge($this->toArray(), [
            'content' => 'Wipps'
        ]);
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
}
