<?php

namespace StatamicRadPack\Runway\Search;

use Illuminate\Database\Eloquent\Model;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Search\Result;
use Statamic\Contracts\Search\Searchable as Contract;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Facades\Site;
use Statamic\Search\Result as ResultInstance;
use StatamicRadPack\Runway\Data\AugmentedModel;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class Searchable implements Augmentable, ContainsQueryableValues, Contract
{
    use ContainsSupplementalData, HasAugmentedInstance;

    protected $model;

    protected $resource;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->resource = Runway::findResourceByModel($model);
        $this->supplements = collect();
    }

    public function get($key, $fallback = null)
    {
        return $this->model->{$key} ?? $fallback;
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function resource(): Resource
    {
        return $this->resource;
    }

    public function getQueryableValue(string $field)
    {
        if ($field === 'site') {
            return Site::current()->handle();
        }

        return $this->model->{$field};
    }

    public function getSearchValue(string $field)
    {
        return $this->model->{$field};
    }

    public function getSearchReference(): string
    {
        return vsprintf('runway::%s::%s', [
            $this->resource->handle(),
            $this->model->getKey(),
        ]);
    }

    public function toSearchResult(): Result
    {
        return new ResultInstance($this, 'runway:'.$this->resource->handle());
    }

    public function getCpSearchResultTitle(): string
    {
        return $this->model->{$this->resource->titleField()};
    }

    public function getCpSearchResultUrl(): string
    {
        return $this->model->runwayEditUrl();
    }

    public function getCpSearchResultBadge(): string
    {
        return $this->resource->name();
    }

    public function newAugmentedInstance(): Augmented
    {
        return (new AugmentedModel($this->model))
            ->supplement($this->supplements());
    }
}
