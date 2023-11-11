<?php

namespace DoubleThreeDigital\Runway\Http\Resources;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $resource = Runway::findResourceByModel($this->resource);

        $with = $resource->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        $augmentedArray = $this->resource
            ->toAugmentedCollection($this->blueprintFields ?? [])
            ->withRelations($with)
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
    public function withBlueprintFields(array $fields)
    {
        $this->blueprintFields = $fields;

        return $this;
    }
}
