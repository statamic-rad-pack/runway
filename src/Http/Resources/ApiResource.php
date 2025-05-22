<?php

namespace StatamicRadPack\Runway\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use StatamicRadPack\Runway\Resource;

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
        $keys = [
            ...$this->blueprintFields->map->handle()->all(),
            ...$this->resource->runwayResource()->nestedFieldPrefixes()->all(),
        ];

        $augmentedArray = $this->resource
            ->toAugmentedCollection($keys)
            ->withRelations($this->blueprintFields->filter->isRelationship()->keys()->all())
            ->withShallowNesting()
            ->toArray();

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
