<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use StatamicRadPack\Runway\Http\Resources\CP\Models;
use StatamicRadPack\Runway\Resource;

class ResourceListingController extends CpController
{
    use QueriesFilters, Traits\HasListingColumns;

    public function index(FilteredRequest $request, Resource $resource)
    {
        $blueprint = $resource->blueprint();

        if (! User::current()->can('view', $resource)) {
            abort(403);
        }

        $query = $resource->model()->with($resource->eagerLoadingRelationships());

        $query->when($query->hasNamedScope('runwayListing'), fn ($query) => $query->runwayListing());

        $searchQuery = $request->search ?? false;

        $query = $this->applySearch($resource, $query, $searchQuery);

        $query->when(method_exists($query, 'getQuery') && $query->getQuery()->orders, function ($query) use ($request) {
            if ($request->input('sort')) {
                $query->reorder($request->input('sort'), $request->input('order'));
            }
        }, fn ($query) => $query->orderBy($request->input('sort', $resource->orderBy()), $request->input('order', $resource->orderByDirection())));

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'resource' => $resource->handle(),
            'blueprints' => [$blueprint],
        ]);

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        if ($searchQuery && $resource->hasSearchIndex()) {
            $results->setCollection($results->getCollection()->map(fn ($item) => $item->getSearchable()->model()));
        }

        return (new Models($results))
            ->runwayResource($resource)
            ->blueprint($resource->blueprint())
            ->setColumnPreferenceKey("runway.{$resource->handle()}.columns")
            ->additional([
                'meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ],
            ]);
    }

    private function applySearch(Resource $resource, $query, $searchQuery)
    {
        if (! $searchQuery) {
            return $query;
        }

        if ($resource->hasSearchIndex() && ($index = $resource->searchIndex())) {
            return $index->ensureExists()->search($searchQuery);
        }

        return $query->runwaySearch($searchQuery);
    }
}
