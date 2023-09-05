<?php

namespace DoubleThreeDigital\Runway\Search;

use DoubleThreeDigital\Runway\Data\AugmentedModel;
use DoubleThreeDigital\Runway\Runway;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Search\Result;
use Statamic\Contracts\Search\Searchable as Contract;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Facades\Site;
use Statamic\Search\Result as ResultInstance;

class Searchable implements Augmentable, ContainsQueryableValues, Contract
{
    use ContainsSupplementalData, HasAugmentedInstance;

    protected $model;

    protected $resource;

    public function __construct($model)
    {
        $this->model = $model;
        $this->resource = Runway::findResourceByModel($model);
    }

    public function resource()
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

    public function getCpSearchResultTitle()
    {
        return $this->model->{$this->resource->titleField()};
    }

    public function getCpSearchResultUrl()
    {
        return cp_route('runway.edit', [$this->resource->handle(), $this->model->getRouteKey()]);
    }

    public function getCpSearchResultBadge()
    {
        return $this->resource->name();
    }

    public function newAugmentedInstance(): Augmented
    {
        return (new AugmentedModel($this->model))
            ->supplement($this->supplements());
    }
}
