<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;
use StatamicRadPack\Runway\Http\Resources\CP\Model as ModelResource;
use StatamicRadPack\Runway\Resource;

class ModelActionController extends ActionController
{
    use Traits\ExtractsFromModelFields;

    protected $resource;

    public function runAction(Request $request, Resource $resource)
    {
        $this->resource = $resource;

        return parent::run($request);
    }

    public function bulkActionsList(Request $request, Resource $resource)
    {
        $this->resource = $resource;

        return parent::bulkActions($request);
    }

    protected function getSelectedItems($items, $context)
    {
        return $this->resource->findMany($items);
    }

    protected function getItemData($model, $context): array
    {
        $model = $model->fresh();

        $blueprint = $this->resource->blueprint();

        [$values] = $this->extractFromFields($model, $this->resource, $blueprint);

        return array_merge((new ModelResource($model))->resolve()['data'], [
            'values' => $values,
            'itemActions' => Action::for($model, $context),
        ]);
    }
}
