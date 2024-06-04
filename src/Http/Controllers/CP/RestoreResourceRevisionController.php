<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Revisions\WorkingCopy;
use StatamicRadPack\Runway\Resource;

class RestoreResourceRevisionController extends CpController
{
    public function __invoke(Request $request, Resource $resource, $model)
    {
        $model = $resource->model()
            ->where($resource->model()->qualifyColumn($resource->routeKey()), $model)
            ->first();

        if (! $target = $model->revision($request->revision)) {
            dd('no such revision', $request->revision);
            // todo: handle invalid revision reference
        }

        if ($model->published()) {
            WorkingCopy::fromRevision($target)->date(now())->save();
        } else {
            $model->makeFromRevision($target)->published(false)->save();
        }

        session()->flash('success', __('Revision restored'));
    }
}
