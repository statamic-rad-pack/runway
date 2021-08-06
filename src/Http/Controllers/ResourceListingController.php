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
    use QueriesFilters;

    public function index(FilteredRequest $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);
        $blueprint = $resource->blueprint();

        if (! User::current()->hasPermission("View {$resource->plural()}") && ! User::current()->isSuper()) {
            abort(403);
        }

        $sortField     = $request->input('sort', $resource->primaryKey());
        $sortDirection = $request->input('order', 'ASC');

        $query = $resource->model()
            ->orderBy($sortField, $sortDirection);

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'collection' => $resourceHandle,
            'blueprints' => [
                $blueprint
            ],
        ]);

        if ($searchQuery = $request->input('search')) {
            $query->where(function ($query) use ($searchQuery, $blueprint) {
                $wildcard = '%'.$searchQuery.'%';

                foreach ($blueprint->fields()->items()->toArray() as $field) {
                    $query->orWhere($field['handle'], 'LIKE', $wildcard);
                }
            });
        }

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        $columns = $this->buildColumns($resource, $blueprint);

        return (new ResourceCollection($results))
            ->setResourceHandle($resourceHandle)
            ->setColumnPreferenceKey('runway.'.$resourceHandle.'.columns')
            ->setColumns($columns)
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }

    /**
     * This method is a duplicate of code in the `ResourceController`.
     * Update both if you make any changes.
     */
    protected function buildColumns($resource, $blueprint)
    {
        return collect($resource->listableColumns())
            ->map(function ($columnKey) use ($resource, $blueprint) {
                $field = $blueprint->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title'  => !$field ? $columnKey : $field->display(),
                    'has_link' => $resource->listableColumns()[0] === $columnKey,
                ];
            })
            ->toArray();
    }
}
