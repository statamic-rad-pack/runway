<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\ActionController;

class ResourceActionController extends ActionController
{
    protected $resource;

    public function runAction(Request $request, $resourceHandle)
    {
        $this->resource = Runway::findResource($resourceHandle);

        return parent::run($request);
    }

    public function bulkActionsList(Request $request, $resourceHandle)
    {
        $this->resource = Runway::findResource($resourceHandle);

        return parent::bulkActions($request);
    }

    protected function getSelectedItems($items, $context)
    {
        return $items->map(fn($item) => $this->resource->find($item)->first());
    }
}
