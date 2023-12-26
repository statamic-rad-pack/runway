<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use StatamicRadPack\Runway\Resource;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\ActionController;

class ResourceActionController extends ActionController
{
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
        return $items->map(fn ($item) => $this->resource->find($item));
    }
}
