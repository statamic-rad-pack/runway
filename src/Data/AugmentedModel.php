<?php

namespace DoubleThreeDigital\Runway\Data;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Collection;
use Statamic\Data\AbstractAugmented;
use Statamic\Fields\Value;

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
            ->merge($this->eloquentRelationships()->values())
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

    protected function eloquentRelationships()
    {
        return $this->resource->eagerLoadingRelations();
    }

    protected function getFromData($handle)
    {
        return $this->supplements[$handle] ?? $this->data->$handle;
    }

    protected function wrapValue($value, $handle)
    {
        $fields = $this->blueprintFields();

        if ($this->resource->eagerLoadingRelations()->flip()->has($handle)) {
            $relatedField = $this->resource->eagerLoadingRelations()->flip()->get($handle);

            return new Value(
                $value,
                $handle,
                optional($fields->get($relatedField))->fieldtype(),
                $this->data
            );
        }

        return new Value(
            $value,
            $handle,
            optional($fields->get($handle))->fieldtype(),
            $this->data
        );
    }
}
