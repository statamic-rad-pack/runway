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

    public function blueprintFields(): Collection
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
        return $this->resource->eloquentRelationships()->reject(fn ($relationship) => $relationship === 'runwayUri');
    }

    protected function getFromData($handle)
    {
        if (Str::contains($handle, '->')) {
            $handle = str_replace('->', '.', $handle);
        }

        return $this->supplements->get($handle) ?? data_get($this->data, $handle);
    }

    public function get($handle): Value
    {
        if ($this->resource->eloquentRelationships()->flip()->has($handle)) {
            $value = $this->wrapEloquentRelationship($handle);

            return $value->resolve();
        }

        if (in_array($handle, $this->nestedFields)) {
            $value = $this->wrapNestedFields($handle);

            return $value->resolve();
        }

        if ($this->hasModelAccessor($handle)) {
            $value = $this->wrapModelAccessor($handle);

            return $value->resolve();
        }

        return parent::get($handle);
    }

    private function wrapEloquentRelationship(string $handle): Value
    {
        $relatedField = $this->resource->eloquentRelationships()->flip()->get($handle);

        return new Value(
            fn () => $this->getFromData($handle),
            $handle,
            $this->fieldtype($relatedField),
            $this->data
        );
    }

    private function wrapNestedFields(string $handle): Value
    {
        return new Value(
            function () use ($handle) {
                $value = $this->getFromData($handle);

                return collect($value)->map(function ($value, $key) use ($handle) {
                    return new Value(
                        $value,
                        $handle,
                        $this->fieldtype("{$handle}->{$key}"),
                        $this->data
                    );
                })->all();
            },
            $handle,
            null,
            $this->data
        );
    }

    private function hasModelAccessor(string $handle): bool
    {
        $method = Str::camel($handle);

        return method_exists($this->data, $method)
            && (new \ReflectionMethod($this->data, $method))->getReturnType()?->getName() === Attribute::class;
    }

    private function wrapModelAccessor(string $handle): Value
    {
        return new Value(
            function () use ($handle) {
                $method = Str::camel($handle);

                $get = $this->data->$method()->get;

                return $get();
            },
            $handle,
            $this->fieldtype($handle),
            $this->data
        );
    }

    private function fieldtype($handle)
    {
        return optional($this->blueprintFields()->get($handle))->fieldtype();
    }
}
