<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Http\Resources\ResourceCollection;
use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
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

        $sortField = $request->input('sort', $resource->primaryKey());
        $sortDirection = $request->input('order', 'ASC');

        $query = $resource->model()
            ->orderBy($sortField, $sortDirection);

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
                    $this->addLikeSearch($blueprint, $searchQuery, $query);
                }
            );
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
    protected function buildColumns(Resource $resource, $blueprint)
    {
        $preferredFirstColumn = isset(Auth::user()->preferences()['runway'][$resource->handle()]['columns'])
            ? Auth::user()->preferences()['runway'][$resource->handle()]['columns'][0]
            : $resource->listableColumns()[0];

        return collect($resource->listableColumns())
            ->map(function ($columnKey) use ($resource, $blueprint, $preferredFirstColumn) {
                $field = $blueprint->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title'  => $field
                        ? $field->display()
                        : $field,
                    'has_link' => $preferredFirstColumn === $columnKey,
                    'is_primary_column' => $preferredFirstColumn === $columnKey,
                ];
            })
            ->toArray();
    }

    private function addLikeSearch(Blueprint $blueprint, string $searchQuery, Builder $query): void
    {
        $searchableFields = $blueprint->fields()->items()->reject(function (array $field) {
            return $field['field']['type'] === 'has_many';
        });

        foreach ($searchableFields as $field) {
            $query->orWhere($field['handle'], 'LIKE', '%' . $searchQuery . '%');
        }
    }
}
