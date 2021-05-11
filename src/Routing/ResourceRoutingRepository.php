<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Models\RunwayUri;

class ResourceRoutingRepository
{
    public function findByUri(string $uri)
    {
        try {
            return RunwayUri::where('uri', $uri)->first()->model;
        } catch (\Exception $e) {
            return null;
        }
    }
}
