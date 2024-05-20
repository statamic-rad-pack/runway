<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;
use StatamicRadPack\Runway\Resource;

class ResourceActionController extends ActionController
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
        $blueprint = $this->resource->blueprint();

        [$values] = $this->extractFromFields($model, $this->resource, $blueprint);

        return [
            'title' => $model->getAttribute($this->resource->titleField()),
            'values' => array_merge($values, ['id' => $model->getKey()]),
            'itemActions' => Action::for($model, $context),
        ];
    }
}
