<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Database\Eloquent\Builder;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Builder as BaseStatamicBuilder;
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

        $query = $resource->newEloquentQueryBuilderWithEagerLoadedRelationships();

        $query->when($query->hasNamedScope('runwayListing'), fn ($query) => $query->runwayListing());

        $searchQuery = $request->search ?? false;

        $query = $this->applySearch($resource, $query, $searchQuery);

        $sortField = $this->getSortableField($resource, $request->input('sort'));
        $sortDirection = $request->input('order', $resource->orderByDirection());

        $query->when(method_exists($query, 'getQuery') && $query->getQuery()->orders, function ($query) use ($sortField, $sortDirection, $resource) {
            if ($sortField) {
                $query->reorder($resource->model()->getColumnForField($sortField), $sortDirection);
            }
        }, fn ($query) => $query->orderBy($resource->model()->getColumnForField($sortField ?? $resource->orderBy()), $sortDirection));

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

    private function applySearch(Resource $resource, Builder $query, string $searchQuery): Builder|BaseStatamicBuilder
    {
        if (! $searchQuery) {
            return $query;
        }

        if ($resource->hasSearchIndex() && ($index = $resource->searchIndex())) {
            return $index->ensureExists()->search($searchQuery);
        }

        return $query->runwaySearch($searchQuery);
    }

    private function getSortableField(Resource $resource, ?string $requestedSort): ?string
    {
        // If no sort was requested, use the resource's default order
        if (! $requestedSort) {
            return null;
        }

        // Check if the requested sort field is sortable
        if ($resource->isFieldSortable($requestedSort)) {
            return $requestedSort;
        }

        // If the requested field is not sortable, fall back to the first sortable column
        return $resource->firstSortableColumn();
    }
}
