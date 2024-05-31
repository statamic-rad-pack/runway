<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\JsonResource;

class Model extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->resource->getKey(),
            'reference' => $this->resource->reference(),
            'resource' => [
                'handle' => $this->resource->runwayResource()->handle(),
            ],
        ];

        return ['data' => $data];
    }
}
