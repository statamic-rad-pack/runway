<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Data\HasAugmentedData;

class RoutingModel implements Augmentable, Responsable
{
    use ContainsSupplementalData, HasAugmentedData;

    public function __construct(protected Model $model)
    {
        $this->supplements = collect();
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

    public function urlWithoutRedirect()
    {
        return $this->uri();
    }

    public function toResponse($request)
    {
        return (new ResourceResponse($this->model))
            ->with($this->supplements)
            ->toResponse($request);
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

    public function __get($key)
    {
        return $this->model->{$key};
    }

    public function augmentedArrayData()
    {
        return $this->model->toArray();
    }
}
