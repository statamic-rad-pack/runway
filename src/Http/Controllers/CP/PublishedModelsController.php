<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Runway\Http\Resources\CP\Model as ModelResource;
use StatamicRadPack\Runway\Resource;

class PublishedModelsController extends CpController
{
    use Traits\ExtractsFromModelFields;

    public function store(Request $request, Resource $resource, $model)
    {
        $model = $resource->model()->where($resource->model()->qualifyColumn($resource->routeKey()), $model)->first();

        $model = $model->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        $blueprint = $model->runwayResource()->blueprint();

        [$values] = $this->extractFromFields($model, $resource, $blueprint);

        return [
            'data' => array_merge((new ModelResource($model->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
        ];
    }

    public function destroy(Request $request, Resource $resource, $model)
    {
        $model = $resource->model()->where($resource->model()->qualifyColumn($resource->routeKey()), $model)->first();

        $model = $model->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        $blueprint = $model->runwayResource()->blueprint();

        [$values] = $this->extractFromFields($model, $resource, $blueprint);

        return [
            'data' => array_merge((new ModelResource($model->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
        ];
    }
}
