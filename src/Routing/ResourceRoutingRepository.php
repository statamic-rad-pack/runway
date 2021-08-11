<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Models\RunwayUri;

class ResourceRoutingRepository
{
    public function findByUri(string $uri)
    {
        // TODO: check model assosiated with model still has routing enabled.

        $runwayUri = RunwayUri::where('uri', $uri)->first();

        if (! $runwayUri) {
            return null;
        }

        return $runwayUri->model->routingModel();
    }
}
