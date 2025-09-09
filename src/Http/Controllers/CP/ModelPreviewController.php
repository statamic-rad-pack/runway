<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\PreviewController;
use Statamic\Support\Arr;
use StatamicRadPack\Runway\Resource;

class ModelPreviewController extends PreviewController
{
    public function create(Request $request, Resource $resource)
    {
        // todo
    }

    public function edit(Request $request, $_, $data)
    {
        //        $this->authorize('view', $data);

        $fields = $data->runwayResource()->blueprint() // we've changed this
            ->fields()
            ->addValues($request->input('preview', []))
            ->process();

        foreach (Arr::except($fields->values()->all(), ['slug']) as $key => $value) {
            $data->setSupplement($key, $value);
        }

        return $this->tokenizeAndReturn($request, $data);
    }
}
