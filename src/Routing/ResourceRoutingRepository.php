<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Models\RunwayUri;
use DoubleThreeDigital\Runway\Runway;
use Statamic\Search\SearchResult;

class ResourceRoutingRepository
{
    public function find($id)
    {
        // used by search in CP
        [$table, $id] = explode('::', $id, 2);

        foreach (Runway::allResources() as $resource) {
            if ($resource->getTable() == $table) {
                $model = $resource->find($id)->first();

                return (new SearchResult)->data([
                    'title' => 'aaa',
                    'edit_url' => 'bbb',
                    'collection' => 'aaaa',
                    'is_entry' => true,
                    'taxonomy' => null,
                    'is_term' => false,
                    'container' => '',
                    'is_asset' => false,
                ]);
            }
        }
    }

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
