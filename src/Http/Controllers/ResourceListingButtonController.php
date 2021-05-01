<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class ResourceListingButtonController extends CpController
{
    public function index(Request $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);
        $listingButton = $resource->listingButtons()[$request->get('listing-button')];

        if (is_callable($listingButton)) {
            return $listingButton($request, $resource);
        }

        return (new $listingButton())->__invoke($request, $resource);
    }
}
