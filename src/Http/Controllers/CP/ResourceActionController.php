<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\ActionController;
use StatamicRadPack\Runway\Runway;

class ResourceActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(fn ($item) => Runway::findResource($item));
    }
}
