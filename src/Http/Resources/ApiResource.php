<?php

namespace StatamicRadPack\Runway\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApiResource extends JsonResource
{
    public $blueprintFields;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $augmentedArray = $this->resource
            ->toAugmentedCollection($this->blueprintFields->map->handle()->all() ?? [])
            ->withRelations($this->blueprintFields->filter->isRelationship()->keys()->all())
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
    public function withBlueprintFields(Collection $fields)
    {
        $this->blueprintFields = $fields;

        return $this;
    }
}
