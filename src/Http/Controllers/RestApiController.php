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

    protected $routeResourceKey = 'handle';

    protected $resourceHandle;

    public function index($handle)
    {
        $this->abortIfDisabled();

        $this->resourceHandle = Str::singular($handle);

        $resource = Runway::findResource($this->resourceHandle);

        if (! $resource) {
            throw new NotFoundHttpException;
        }

        $results = $this->filterSortAndPaginate($resource->model()->query());

        $results = ApiResource::collection($results);

        $results->setCollection(
            $results->getCollection()
                ->transform(fn ($result) => $result->withBlueprintFields($this->getFieldsFromBlueprint($resource)))
        );

        return $results;
    }

    public function show($handle, $id)
    {
        $this->abortIfDisabled();

        $this->resourceHandle = Str::singular($handle);

        $resource = Runway::findResource($this->resourceHandle);

        if (! $resource) {
            throw new NotFoundHttpException;
        }

        if (! $model = $resource->model()->find($id)) {
            throw new NotFoundHttpException;
        }

        return ApiResource::make($model)->withBlueprintFields($this->getFieldsFromBlueprint($resource));
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', $this->resourceConfigKey, Str::plural($this->resourceHandle));
    }

    private function getFieldsFromBlueprint(Resource $resource): array
    {
        return $resource->blueprint()->fields()->all()->map->handle()->all();
    }
}
