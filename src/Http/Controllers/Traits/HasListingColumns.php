<?php

namespace DoubleThreeDigital\Runway\Http\Controllers\Traits;

use DoubleThreeDigital\Runway\Resource;
use Statamic\Facades\User;

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
        if (isset(User::current()->preferences()['runway'][$resource->handle()]['columns'])) {
            return collect($resource->blueprint()->fields()->all())
                ->filter(fn ($field) => in_array($field->handle(), User::current()->preferences()['runway'][$resource->handle()]['columns']))
                ->reject(function ($field) {
                    return $field->fieldtype()->indexComponent() === 'relationship'
                        || $field->fieldtype()->indexComponent() === 'hasmany-related-item';
                })
                ->map(fn ($field) => $field->handle())
                ->first();
        }

        return $resource->titleField();
    }
}
