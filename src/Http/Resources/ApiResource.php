<?php

namespace DoubleThreeDigital\Runway\Http\Resources;

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
        return array_merge([
            $this->resource->getKeyName() => $this->resource->getKey(),
        ], $this->resource
            ->toAugmentedArray($this->blueprintFields)
        );
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
