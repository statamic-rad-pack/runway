<?php

namespace DuncanMcClean\Runway\Routing;

use DuncanMcClean\Runway\Models\RunwayUri;

class ResourceRoutingRepository
{
    public function findByUri(string $uri)
    {
        $runwayUri = RunwayUri::where('uri', $uri)->first();

        if (! $runwayUri) {
            return null;
        }

        return $runwayUri->model->routingModel();
    }
}
