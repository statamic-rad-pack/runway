<?php

namespace DoubleThreeDigital\Runway\Tags;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Statamic\Tags\Tags;

class RunwayTag extends Tags
{
    protected static $handle = 'runway';

    public function wildcard($resourceHandle = null)
    {
        $resource = Runway::findResource(
            $this->params->has('resource') ? $this->params->get('resource') : $resourceHandle
        );

        $blueprint = $resource->blueprint();

        $query = $resource->model()->query();

        if ($this->params->has('where') && $where = $this->params->get('where')) {
            $query->where(explode(':', $where)[0], explode(':', $where)[1]);
        }

        if ($this->params->has('sort')) {
            $sortColumn = explode(':', $this->params->get('sort'))[0];
            $sortDirection = explode(':', $this->params->get('sort'))[1];

            $query->orderBy($sortColumn, $sortDirection);
        }

        if ($this->params->get('paginate') || $this->params->get('limit')) {
            $paginator = $query->paginate($this->params->get('limit'));
            $results = $paginator->items();
        } else {
            $results = $query->get();
        }

        if (! $this->params->has('as')) {
            return $this->augmentRecords($results, $resource);
        }

        return [
            $this->params->get('as') => $this->augmentRecords($results, $resource),
            'paginate'   => isset($paginator) ? $paginator->toArray() : null,
            'no_results' => collect($results)->isEmpty(),
        ];
    }

    protected function augmentRecords($query, Resource $resource)
    {
        return collect($query)
            ->map(function ($record, $key) use ($resource) {
                return $resource->augment($record);
            })
            ->toArray();
    }
}
