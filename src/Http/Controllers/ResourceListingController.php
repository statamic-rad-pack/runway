<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Http\Resources\ResourceCollection;
use DoubleThreeDigital\Runway\Runway;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class ResourceListingController extends CpController
{
    use QueriesFilters, Traits\HasListingColumns;

    public function index(FilteredRequest $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);
        $blueprint = $resource->blueprint();

        if (! User::current()->hasPermission("view {$resource->handle()}") && ! User::current()->isSuper()) {
            abort(403);
        }

        $sortField = $request->input('sort', $resource->orderBy());
        $sortDirection = $request->input('order', $resource->orderByDirection());

        $query = $resource->model()
            ->orderBy($sortField, $sortDirection);

        if ($query->hasNamedScope('runwayListing')) {
            $query->runwayListing();
        }

        $query->with($resource->eagerLoadingRelations()->values()->all());

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'collection' => $resourceHandle,
            'blueprints' => [
                $blueprint,
            ],
        ]);

        if ($searchQuery = $request->input('search')) {
            $query->when(
                $query->hasNamedScope('runwaySearch'),
                function ($query) use ($searchQuery) {
                    $query->runwaySearch($searchQuery);
                },
                function ($query) use ($searchQuery, $blueprint) {
                    $blueprint->fields()->items()
                        ->reject(function (array $field) {
                            return $field['field']['type'] === 'has_many'
                                || $field['field']['type'] === 'hidden';
                        })
                        ->each(function (array $field) use ($query, $searchQuery) {
                            $query->orWhere($field['handle'], 'LIKE', '%' . $searchQuery . '%');
                        });
                }
            );
        }

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        $columns = $this->buildColumns($resource, $blueprint);

        return (new ResourceCollection($results))
            ->setResourceHandle($resourceHandle)
            ->setColumnPreferenceKey('runway.' . $resourceHandle . '.columns')
            ->setColumns($columns)
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }
}
