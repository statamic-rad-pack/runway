<?php

namespace DoubleThreeDigital\Runway\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ApiResource extends JsonResource
{
    public $blueprintFields = [];

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $with = $this->resource->runwayResource()->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        $augmentedArray = $this->resource
            ->toAugmentedCollection($this->blueprintFields ?? [])
            ->withRelations($with)
            ->withShallowNesting()
            ->toArray();

        collect($augmentedArray)
            ->filter(fn ($value, $key) => Str::contains($key, '->'))
            ->each(function ($value, $key) use (&$augmentedArray) {
                $augmentedArray[Str::before($key, '->')][Str::after($key, '->')] = $value;
                unset($augmentedArray[$key]);
            });

        return array_merge($augmentedArray, [
            $this->resource->getKeyName() => $this->resource->getKey(),
        ]);
    }

    /**
     * Set the fields that should be returned by this resource
     *
     * @return self
     */
    public function withBlueprintFields(array $fields)
    {
        $this->blueprintFields = $fields;

        return $this;
    }
}
