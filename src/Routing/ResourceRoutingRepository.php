<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Models\RunwayUri;
use DoubleThreeDigital\Runway\Runway;
use Statamic\Search\SearchResult;

class ResourceRoutingRepository
{
    public function find($id)
    {
        [$resourceHandle, $id] = explode('::', $id, 2);

        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->firstWhere($resource->primaryKey(), $id);

        if (!$record) {
            return null;
        }

        return (new SearchResult)->data([
            'title' => $record->{collect($resource->listableColumns())->first()},
            'edit_url' => cp_route('runway.edit', [
                'resourceHandle' => $resource->handle(),
                'record' => $record->getRouteKey(),
            ]),
            'collection' => $resource->name(),
            'is_entry' => true,
            'taxonomy' => null,
            'is_term' => false,
            'container' => '',
            'is_asset' => false,
        ]);
    }

    public function findByUri(string $uri)
    {
        // TODO: check model assosiated with model still has routing enabled.

        $runwayUri = RunwayUri::where('uri', $uri)->first();

        if (!$runwayUri) {
            return null;
        }

        return $runwayUri->model->routingModel();
    }
}
