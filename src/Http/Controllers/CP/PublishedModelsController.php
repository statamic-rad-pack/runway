<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Runway\Http\Resources\CP\Model as ModelResource;
use StatamicRadPack\Runway\Resource;

class PublishedModelsController extends CpController
{
    use Traits\ExtractsFromModelFields;

    public function store(Request $request, Resource $resource, Model $model)
    {
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
            'saved' => true,
        ];
    }

    public function destroy(Request $request, Resource $resource, Model $model)
    {
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
            'saved' => true,
        ];
    }
}
