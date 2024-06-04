<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\JsonResource;

class Model extends JsonResource
{
    public function toArray($request)
    {
        $runwayResource = $this->resource->runwayResource();

        $data = [
            'id' => $this->resource->getKey(),
            'reference' => $this->resource->reference(),
            'title' => $this->resource->{$runwayResource->titleField()},
            'permalink' => $runwayResource->hasRouting() ? $this->resource->absoluteUrl() : null,
            'published' => $this->resource->publishedStatus(),
            'edit_url' => cp_route('runway.edit', [
                'resource' => $runwayResource->handle(),
                'model' => $this->resource->{$runwayResource->routeKey()},
            ]),
            'resource' => [
                'handle' => $runwayResource->handle(),
            ],
        ];

        return ['data' => $data];
    }
}
