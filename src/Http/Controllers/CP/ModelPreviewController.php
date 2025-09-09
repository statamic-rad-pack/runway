<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\PreviewController;
use Statamic\Support\Arr;

class ModelPreviewController extends PreviewController
{
    public function edit(Request $request, $_, $data)
    {
        $this->authorize('edit', [$data->runwayResource(), $data]);

        $fields = $data->runwayResource()->blueprint()
            ->fields()
            ->addValues($request->input('preview', []))
            ->process();

        foreach (Arr::except($fields->values()->all(), ['slug']) as $key => $value) {
            $data->setSupplement($key, $value);
        }

        return $this->tokenizeAndReturn($request, $data);
    }
}
