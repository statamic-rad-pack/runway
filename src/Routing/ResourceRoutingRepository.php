<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Models\RunwayUri;

class ResourceRoutingRepository
{
    public function findByUri(string $uri)
    {
        return RunwayUri::where('uri', $uri)->first()->model;
    }
}
