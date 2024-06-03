<?php

namespace StatamicRadPack\Runway\Routing;

class ResourceRoutingRepository
{
    public function findByUri(string $uri)
    {
        $runwayUri = RunwayUri::where('uri', $uri)->first();

        if (! $runwayUri) {
            return null;
        }

        if ($runwayUri->model->runwayResource()->hasPublishStates() && $runwayUri->model->publishedStatus() !== 'published') {
            return null;
        }

        return $runwayUri->model->routingModel();
    }
}
