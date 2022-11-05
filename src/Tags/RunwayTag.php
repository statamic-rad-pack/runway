<?php

namespace DoubleThreeDigital\Runway\Tags;

use DoubleThreeDigital\Runway\Exceptions\ResourceNotFound;
use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Str;
use Statamic\Tags\Tags;

class RunwayTag extends Tags
{
    protected static $handle = 'runway';

    public function wildcard($resourceHandle = null)
    {
        try {
            $resource = Runway::findResource(
                $this->params->has('resource') ? Str::studly($this->params->get('resource')) : Str::studly($resourceHandle)
            );
        } catch (ResourceNotFound) {
            $resource = Runway::findResource(
                $this->params->has('resource') ? Str::lower($this->params->get('resource')) : Str::lower($resourceHandle)
            );
        }

        $query = $resource->model()->query();

        if ($scopes = $this->params->get('scope')) {
            $scopes = explode('|', (string) $scopes);

            foreach ($scopes as $scope) {
                $scopeName = explode(':', $scope)[0];
                $scopeArguments = isset(explode(':', $scope)[1])
                    ? explode(',', explode(':', $scope)[1])
                    : [];

                foreach ($scopeArguments as $key => $scopeArgument) {
                    if ($fromContext = $this->context->get($scopeArgument)) {
                        if ($fromContext instanceof \Statamic\Fields\Value) {
                            $fromContext = $fromContext->raw();
                        }

                        $scopeArguments[$key] = $fromContext;
                    }
                }

                $query->{$scopeName}(...$scopeArguments);
            }
        }

        if ($this->params->has('where') && $where = $this->params->get('where')) {
            $query->where(explode(':', (string) $where)[0], explode(':', (string) $where)[1]);
        }

        if ($with = $this->params->get('with')) {
            $query->with(explode('|', (string) $with));
        }

        if ($this->params->has('sort')) {
            $sortColumn = explode(':', (string) $this->params->get('sort'))[0];
            $sortDirection = explode(':', (string) $this->params->get('sort'))[1];

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
            ->map(fn ($record, $key) => $resource->augment($record))
            ->toArray();
    }
}
