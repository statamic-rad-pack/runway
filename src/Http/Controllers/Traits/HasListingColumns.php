<?php

namespace DoubleThreeDigital\Runway\Http\Controllers\Traits;

use DoubleThreeDigital\Runway\Resource;

trait HasListingColumns
{
    protected function buildColumns(Resource $resource, $blueprint)
    {
        return collect($resource->listableColumns())
            ->map(function ($columnKey) use ($blueprint) {
                $field = $blueprint->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title' => $field ? $field->display() : $field,
                ];
            })
            ->toArray();
    }
}
