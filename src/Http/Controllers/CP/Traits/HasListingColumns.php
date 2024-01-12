<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP\Traits;

use Illuminate\Support\Arr;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use StatamicRadPack\Runway\Resource;

trait HasListingColumns
{
    protected function buildColumns(Resource $resource, $blueprint): array
    {
        return $resource->listableColumns()
            ->map(function ($columnKey) use ($blueprint) {
                $field = $blueprint->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title' => $field ? $field->display() : $field,
                ];
            })
            ->toArray();
    }

    protected function getPrimaryColumn(Resource $resource): string
    {
        if (Arr::has(User::current()->preferences(), "runway.{$resource->handle()}.columns")) {
            return collect($resource->blueprint()->fields()->all())
                ->filter(function (Field $field) use ($resource) {
                    return in_array($field->handle(), Arr::get(User::current()->preferences(), "runway.{$resource->handle()}.columns"));
                })
                ->reject(function (Field $field) {
                    return $field->fieldtype()->indexComponent() === 'relationship' || $field->type() === 'section';
                })
                ->map->handle()
                ->first();
        }

        return $resource->titleField();
    }
}
