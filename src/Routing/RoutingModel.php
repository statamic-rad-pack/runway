<?php

namespace StatamicRadPack\Runway\Routing;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Data\HasAugmentedData;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class RoutingModel implements Augmentable, Responsable
{
    use ContainsSupplementalData, HasAugmentedData;

    public function __construct(protected Model $model)
    {
        $this->supplements = collect();
    }

    public function route(): ?string
    {
        if (! $this->model->runwayUri) {
            return null;
        }

        return $this->model->runwayUri->uri;
    }

    public function routeData(): array
    {
        return [
            'id' => $this->model->{$this->model->getKeyName()},
        ];
    }

    public function uri(): ?string
    {
        return $this->model->routableUri();
    }

    public function urlWithoutRedirect(): ?string
    {
        return $this->uri();
    }

    public function toResponse($request)
    {
        return (new ResourceResponse($this->model))
            ->with($this->supplements)
            ->toResponse($request);
    }

    public function resource(): ?Resource
    {
        return Runway::findResourceByModel($this->model);
    }

    public function template(): string
    {
        return $this->resource()->template();
    }

    public function layout(): string
    {
        return $this->resource()->layout();
    }

    public function id()
    {
        return $this->model->getKey();
    }

    public function getRouteKey()
    {
        return $this->model->getAttributeValue($this->model->getRouteKeyName());
    }

    public function augmentedArrayData()
    {
        return $this->model->toArray();
    }

    public function __get($key)
    {
        return $this->model->{$key};
    }
}
