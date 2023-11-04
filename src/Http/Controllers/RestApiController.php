<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Http\Resources\ApiResource;
use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Facades\Statamic\API\FilterAuthorizer;
use Illuminate\Support\Str;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\API\ApiController;

class RestApiController extends ApiController
{
    private $config;

    protected $resourceConfigKey = 'runway';

    protected $routeResourceKey = 'resource';

    protected $resourceHandle;

    public function index($resource)
    {
        $this->abortIfDisabled();

        $this->resourceHandle = Str::singular($resource);

        $resource = Runway::findResource($this->resourceHandle);

        if (! $resource) {
            throw new NotFoundHttpException;
        }

        $results = $this->filterSortAndPaginate($resource->model()->query());
        $results->setCollection($results->getCollection()->map(fn ($model) => $this->makeResourceFromModel($resource, $model)));

        return ApiResource::collection($results);
    }

    public function show($resource, $id)
    {
        $this->abortIfDisabled();

        $this->resourceHandle = Str::singular($resource);

        $resource = Runway::findResource($this->resourceHandle);

        if (! $resource) {
            throw new NotFoundHttpException;
        }

        if (! $model = $resource->model()->find($id)) {
            throw new NotFoundHttpException;
        }

        return ApiResource::make($this->makeResourceFromModel($resource, $model));
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', $this->resourceConfigKey, Str::plural($this->resourceHandle));
    }

    private function makeResourceFromModel($resource, $model)
    {
        if (! $this->config) {
            $this->config = collect(config('runway.resources'))->get(get_class($model));
        }

        return new Resource(
            handle: $this->resourceHandle,
            model: $model,
            name: $this->config['name'] ?? Str::title($this->resourceHandle),
            blueprint: $resource->blueprint(),
            config: $this->config ?? [],
        );
    }
}
