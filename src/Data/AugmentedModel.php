<?php

namespace StatamicRadPack\Runway\Data;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Data\AbstractAugmented;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Statamic;
use StatamicRadPack\Runway\Runway;

class AugmentedModel extends AbstractAugmented
{
    protected $data;

    protected $resource;

    protected $supplements;

    protected $nestedFields = [];

    public function __construct($model)
    {
        $this->data = $model;
        $this->resource = Runway::findResourceByModel($model);
        $this->supplements = collect();
    }

    public function supplement(Collection $data)
    {
        $this->supplements = $data;

        return $this;
    }

    public function keys()
    {
        return collect()
            ->merge($this->modelAttributes()->keys())
            ->merge($this->appendedAttributes()->values())
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

    public function apiUrl()
    {
        if (! $id = $this->data->{$this->resource->primaryKey()}) {
            return null;
        }

        return Statamic::apiRoute('runway.show', [$this->resource->handle(), $id]);
    }

    protected function modelAttributes(): Collection
    {
        return collect($this->data->getAttributes());
    }

    protected function appendedAttributes(): Collection
    {
        return collect($this->data->getAppends());
    }

    protected function blueprintFields(): Collection
    {
        $fields = $this->resource->blueprint()->fields()->all();

        $this->nestedFields = $fields
            ->filter(fn (Field $field) => Str::contains($field->handle(), '->'))
            ->map(fn (Field $field) => Str::before($field->handle(), '->'))
            ->unique()
            ->toArray();

        return $fields;
    }

    protected function eloquentRelationships()
    {
        return $this->resource->eloquentRelationships();
    }

    protected function getFromData($handle)
    {
        if (Str::contains($handle, '->')) {
            $handle = str_replace('->', '.', $handle);
        }

        return $this->supplements->get($handle) ?? data_get($this->data, $handle);
    }

    protected function wrapValue($value, $handle)
    {
        $fields = $this->blueprintFields();

        if ($this->resource->eloquentRelationships()->flip()->has($handle)) {
            $relatedField = $this->resource->eloquentRelationships()->flip()->get($handle);

            return new Value(
                $value,
                $handle,
                optional($fields->get($relatedField))->fieldtype(),
                $this->data
            );
        }

        if (in_array($handle, $this->nestedFields)) {
            $value = collect($value)
                ->map(function ($value, $key) use ($handle, $fields) {
                    $field = $fields->get("{$handle}->{$key}");

                    return new Value(
                        $value,
                        $handle,
                        $field->fieldtype(),
                        $this->data,
                    );
                })
                ->toArray();
        }

        if ($value instanceof Attribute) {
            $value = ($value->get)();
        }

        return new Value(
            $value,
            $handle,
            optional($fields->get($handle))->fieldtype(),
            $this->data
        );
    }
}
