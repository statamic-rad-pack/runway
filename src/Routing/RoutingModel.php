<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;

class RoutingModel implements Responsable
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function route()
    {
        if (! $this->model->runwayUri) {
            return null;
        }

        return $this->model->runwayUri->uri;
    }

    public function routeData()
    {
        return [
            'id' => $this->model->{$this->model->getKeyName()},
        ];
    }

    public function uri()
    {
        return $this->model->routableUri();
    }

    public function toResponse($request)
    {
        return (new ResourceResponse($this->model))->toResponse($request);
    }

    public function template(): string
    {
        return Runway::findResourceByModel($this->model)->template();
    }

    public function layout(): string
    {
        return Runway::findResourceByModel($this->model)->layout();
    }

    public function id()
    {
        return $this->model->getKey();
    }

    public function getRouteKey()
    {
        return $this->model->getAttributeValue($this->model->getRouteKeyName());
    }
}
