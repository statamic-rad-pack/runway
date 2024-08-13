<?php

namespace StatamicRadPack\Runway\Tags;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Blink;
use Statamic\Tags\Tags;
use StatamicRadPack\Runway\Exceptions\ResourceNotFound;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class RunwayTag extends Tags
{
    protected static $handle = 'runway';

    public function wildcard($resourceHandle = null): array
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

        $query = $resource->model()->query()
            ->when(
                $this->params->get('status'),
                fn ($query, $status) => $query->whereStatus($status),
                fn ($query) => $query->whereStatus('published')
            )
            ->when(
                $this->params->get('with'),
                fn ($query) => $query->with(explode('|', (string) $this->params->get('with'))),
                fn ($query) => $query->with($resource->eagerLoadingRelationships())
            );

        if ($select = $this->params->get('select')) {
            $query->select(explode(',', (string) $select));
        }

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
            $key = explode(':', (string) $where)[0];
            $value = explode(':', (string) $where)[1];

            if ($resource->eloquentRelationships()->has($key)) {
                // eloquentRelationships() returns a Collection of keys/values, the keys are the field names
                // & the values are the Eloquent relationship names. We need to get the relationship name
                // for the whereHas query.
                $relationshipName = $resource->eloquentRelationships()->get($key);
                $relationshipResource = Runway::findResource($resource->blueprint()->field($key)->config()['resource']);

                $query->whereHas($relationshipName, function ($query) use ($value, $relationshipResource) {
                    $query->whereIn($relationshipResource->databaseTable().'.'.$relationshipResource->primaryKey(), Arr::wrap($value));
                });
            } else {
                $query->where($key, $value);
            }
        }

        if ($this->params->has('sort') && ! empty($this->params->get('sort'))) {
            if (Str::contains($this->params->get('sort'), ':')) {
                $sortColumn = explode(':', (string) $this->params->get('sort'))[0];
                $sortDirection = explode(':', (string) $this->params->get('sort'))[1];
            } else {
                $sortColumn = $this->params->get('sort');
                $sortDirection = 'asc';
            }

            $query->orderBy($sortColumn, $sortDirection);
        }

        if ($this->params->get('paginate') || $this->params->get('limit')) {
            $paginator = $query->paginate($this->params->get('limit'));

            $paginator = app()->makeWith(LengthAwarePaginator::class, [
                'items' => $paginator->items(),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'options' => $paginator->getOptions(),
            ])->withQueryString();

            $results = $paginator->items();
        } else {
            $results = $query->get();
        }

        if (! $this->params->has('as')) {
            return $this->augmentModels($results, $resource);
        }

        return [
            $this->params->get('as') => $this->augmentModels($results, $resource),
            'paginate' => isset($paginator) ? $this->getPaginationData($paginator) : null,
            'no_results' => collect($results)->isEmpty(),
        ];
    }

    public function count(): int
    {
        $resourceHandle = $this->params->get('in');
        if (! $resourceHandle) {
            return 0;
        }

        try {
            $resource = Runway::findResource(
                $this->params->has('resource') ? Str::studly($this->params->get('resource')) : Str::studly($resourceHandle)
            );
        } catch (ResourceNotFound) {
            $resource = Runway::findResource(
                $this->params->has('resource') ? Str::lower($this->params->get('resource')) : Str::lower($resourceHandle)
            );
        }

        return $resource->model()->query()->count();
    }

    protected function augmentModels($query, Resource $resource): array
    {
        return collect($query)
            ->map(function ($model, $key) use ($resource) {
                return Blink::once("Runway::Tag::AugmentModels::{$resource->handle()}::{$model->{$resource->primaryKey()}}", function () use ($model) {
                    return $model->toAugmentedArray();
                });
            })
            ->toArray();
    }

    protected function getPaginationData($paginator): array
    {
        return [
            'total_items' => $paginator->total(),
            'items_per_page' => $paginator->perPage(),
            'total_pages' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'prev_page' => $paginator->previousPageUrl(),
            'next_page' => $paginator->nextPageUrl(),
            'auto_links' => $paginator->render('pagination::default'),
            'links' => $paginator->renderArray(),
        ];
    }
}
