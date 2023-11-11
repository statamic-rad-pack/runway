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
        // this gets around augmented model wrapValue() returning handles
        // like meta->title instead of a nested array
        // if that ever changes this could be removed
        $augmentedArray = collect($this->resource->toAugmentedArray($this->blueprintFields))
            ->mapWithKeys(fn ($item, $key) => [str_replace('->', '.', $key) => $item])
            ->undot()
            ->all();

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
