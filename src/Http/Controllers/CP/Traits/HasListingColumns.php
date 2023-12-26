<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP\Traits;

use StatamicRadPack\Runway\Resource;
use Illuminate\Support\Arr;
use Statamic\Facades\User;
use Statamic\Fields\Field;

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
                ->filter(fn (Field $field) => in_array(Arr::get(User::current()->preferences(), "runway.{$resource->handle()}.columns"), $field->handle()))
                ->reject(fn (Field $field) => $field->fieldtype()->indexComponent() === 'relationship')
                ->map->handle()
                ->first();
        }

        return $resource->titleField();
    }
}
