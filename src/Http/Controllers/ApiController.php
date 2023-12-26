<?php

namespace StatamicRadPack\Runway\Http\Controllers;

use StatamicRadPack\Runway\Http\Resources\ApiResource;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;
use Facades\Statamic\API\FilterAuthorizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\API\ApiController as StatamicApiController;

class ApiController extends StatamicApiController
{
    private $config;

    protected $resourceConfigKey = 'runway';

    protected $routeResourceKey = 'resourceHandle';

    protected $resourceHandle;

    public function index($resourceHandle)
    {
        $this->abortIfDisabled();

        $this->resourceHandle = Str::singular($resourceHandle);

        $resource = Runway::findResource($this->resourceHandle);

        if (! $resource) {
            throw new NotFoundHttpException;
        }

        $results = $this->filterSortAndPaginate($resource->model()->query());

        $results = ApiResource::collection($results);

        $results->setCollection(
            $results->getCollection()->transform(fn ($result) => $result->withBlueprintFields($this->getFieldsFromBlueprint($resource)))
        );

        return $results;
    }

    public function show($resourceHandle, $model)
    {
        $this->abortIfDisabled();

        $this->resourceHandle = Str::singular($resourceHandle);

        $resource = Runway::findResource($this->resourceHandle);

        if (! $resource) {
            throw new NotFoundHttpException;
        }

        if (! $model = $resource->model()->find($model)) {
            throw new NotFoundHttpException;
        }

        return ApiResource::make($model)->withBlueprintFields($this->getFieldsFromBlueprint($resource));
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', $this->resourceConfigKey, Str::plural($this->resourceHandle));
    }

    private function getFieldsFromBlueprint(Resource $resource): Collection
    {
        return $resource->blueprint()->fields()->all();
    }
}
