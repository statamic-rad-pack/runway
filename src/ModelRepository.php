<?php

namespace StatamicRadPack\Runway;

use Illuminate\Support\Arr;
use StatamicRadPack\Runway\Routing\RunwayUri;

class ModelRepository
{
    protected $substitutionsById = [];
    protected $substitutionsByUri = [];

    public function findByUri(string $uri)
    {
        if ($substitute = Arr::get($this->substitutionsByUri, $uri)) {
            return $substitute?->routingModel();
        }

        $runwayUri = RunwayUri::firstWhere('uri', $uri);
        $model = $runwayUri?->model;

        if (! $runwayUri || ! $model) {
            return null;
        }

        if (
            $model->runwayResource()->hasPublishStates()
            && $model->publishedStatus() !== 'published'
        ) {
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
        return $items->map(function ($item) {
            return $this->substitutionsById[$item->reference()] ?? $item;
        });
    }
}
