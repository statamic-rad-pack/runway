<?php

namespace DoubleThreeDigital\Runway\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge([
            $this->resource->primaryKey() => $this->resource->model()->getKey(),
        ], $this->resource
            ->toAugmentedCollection()
            ->withShallowNesting()
            ->toArray()
        );
    }
}
