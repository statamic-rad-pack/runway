<?php

namespace DoubleThreeDigital\Runway\Tags;

use DoubleThreeDigital\Runway\AugmentedRecord;
use DoubleThreeDigital\Runway\Support\ModelFinder;
use Statamic\Fields\Field;
use Statamic\Tags\Tags;

class RunwayTag extends Tags
{
    protected static $handle = 'runway';

    public function wildcard($model = null)
    {
        $model = ModelFinder::find(
            $this->params->has('model') ? $this->params->get('model') : $model
        );

        $blueprint = $model['blueprint'];

        $query = (new $model['model']())->query();

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
            return $this->augmentRecords($results, $blueprint);
        }

        return [
            $this->params->get('as') => $this->augmentRecords($results, $blueprint),
            'paginate'   => isset($paginator) ? $paginator->toArray() : null,
            'no_results' => collect($results)->isEmpty(),
        ];
    }

    protected function augmentRecords($query, $blueprint)
    {
        return collect($query)
            ->map(function ($record, $key) use ($blueprint) {
                return $this->augmentRecord($record, $blueprint);
            })
            ->toArray();
    }

    // TODO: replace calls to this method with the real deal
    protected function augmentRecord($record, $blueprint)
    {
        return AugmentedRecord::augment($record, $blueprint);
    }
}
