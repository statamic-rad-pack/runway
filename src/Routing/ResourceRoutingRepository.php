<?php

namespace StatamicRadPack\Runway\Routing;

class ResourceRoutingRepository
{
    protected $substitutionsById = [];
    protected $substitutionsByUri = [];

    public function findByUri(string $uri)
    {
        $runwayUri = RunwayUri::where('uri', $uri)->first();

        if (! $runwayUri) {
            return null;
        }

        $model = $this->substitutionsById[$runwayUri->model->reference()] ?? $runwayUri->model;

        if ($model->runwayResource()->hasPublishStates() && $model->publishedStatus() !== 'published') {
            return null;
        }

        return $model->routingModel();
    }

    public function substitute($item)
    {
        $this->substitutionsById[$item->reference()] = $item;
        $this->substitutionsByUri[$item->uri()] = $item;
    }

    public function applySubstitutions($items)
    {
        throw new \Exception('Method not implemented. Models are substitited in findByUri.');
    }
}
