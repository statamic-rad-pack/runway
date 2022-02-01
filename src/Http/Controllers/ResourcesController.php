<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Runway;
use Statamic\Http\Controllers\CP\CpController;

class ResourcesController extends CpController
{
    public function index()
    {
        $resourcesAuthorized = Runway::allResourcesAuthorized();
        if ($resourcesAuthorized->isEmpty()) {
            return redirect(cp_route('dashboard'));
        }
        return redirect(cp_route('runway.index', [
            'resourceHandle' => $resourcesAuthorized->first()->handle()
        ]));
    }
    
}
