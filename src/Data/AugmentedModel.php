<?php

namespace DoubleThreeDigital\Runway\Data;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Collection;
use Statamic\Data\AbstractAugmented;

class AugmentedModel extends AbstractAugmented
{
    protected $data;

    protected $resource;

    protected $supplements = [];

    public function __construct($model)
    {
        $this->data = $model;
        $this->resource = Runway::findResourceByModel($model);
    }

    public function supplement(array $data)
    {
        $this->supplements = $data;

        return $this;
    }

    public function keys()
    {
        return collect()
            ->merge($this->modelAttributes()->keys())
            ->merge($this->blueprintFields()->keys())
            ->merge($this->commonKeys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys()
    {
        $commonKeys = [];

        if ($this->resource->hasRouting()) {
            $commonKeys[] = 'url';
        }

        return $commonKeys;
    }

    public function url(): ?string
    {
        return $this->resource->hasRouting()
            ? $this->data->uri()
            : null;
    }

    protected function modelAttributes(): Collection
    {
        return collect($this->data->getAttributes());
    }

    protected function blueprintFields(): Collection
    {
        return $this->resource->blueprint()->fields()->all();
    }

    protected function getFromData($handle)
    {
        return $this->supplements[$handle] ?? $this->data->$handle;
    }
}
