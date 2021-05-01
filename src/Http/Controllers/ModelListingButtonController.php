<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class ModelListingButtonController extends CpController
{
    public function index(Request $request, $model)
    {
        $resource = Runway::findResource($model);
        $listingButton = $resource->listingButtons()[$request->get('listing-button')];

        if (is_callable($listingButton)) {
            return $listingButton($request, $resource);
        }

        return (new $listingButton())->__invoke($request, $resource);
    }
}
