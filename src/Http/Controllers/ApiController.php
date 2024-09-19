<?php

namespace StatamicRadPack\Runway\Http\Controllers;

use Facades\Statamic\API\FilterAuthorizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\API\ApiController as StatamicApiController;
use StatamicRadPack\Runway\Exceptions\ResourceNotFound;
use StatamicRadPack\Runway\Http\Resources\ApiResource;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class ApiController extends StatamicApiController
{
    private $config;

    protected $resourceConfigKey = 'runway';
    protected $routeResourceKey = 'resourceHandle';
    protected $filterPublished = true;
    protected $resourceHandle;

    public function index($resourceHandle)
    {
        $this->abortIfDisabled();

        $resource = $this->resource($resourceHandle);
        $this->resourceHandle = $resource->handle();

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

        $resource = $this->resource($resourceHandle);
        $this->resourceHandle = $resource->handle();

        if (! $model = $resource->model()->whereStatus('published')->find($model)) {
            throw new NotFoundHttpException;
        }

        return ApiResource::make($model)->withBlueprintFields($this->getFieldsFromBlueprint($resource));
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', $this->resourceConfigKey, Str::plural($this->resourceHandle));
    }

    private function resource(string $resourceHandle): Resource
    {
        try {
            try {
                return Runway::findResource($resourceHandle);
            } catch (ResourceNotFound $e) {
                return Runway::findResource(Str::singular($resourceHandle));
            }
        } catch (ResourceNotFound $e) {
            throw new NotFoundHttpException;
        }
    }

    private function getFieldsFromBlueprint(Resource $resource): Collection
    {
        return $resource->blueprint()->fields()->all();
    }
}
