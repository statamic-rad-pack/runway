<?php

namespace StatamicRadPack\Runway;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Fields\Blueprint;

class GenerateBlueprint
{
    protected static array $columnMappings = [
        'integer' => 'integer',
        'tinyint' => 'integer',
        'bigint' => 'integer',
        'varchar' => 'text',
        'text' => 'textarea',
//        'json' => 'array',
        'timestamp' => 'date',
    ];

    public static function generate(Resource $resource): Blueprint
    {
        $mainSection = [];
        $sidebarSection = [];

        collect(Schema::getColumns($resource->databaseTable()))
            ->reject(fn (array $column) => in_array($column['name'], ['id', 'created_at', 'updated_at']))
            ->map(fn (array $column) => [
                'name' => $column['name'],
                'type' => static::getMatchingFieldtype($column),
                'nullable' => $column['nullable'],
                'default' => $column['default'],
            ])
            ->reject(fn (array $field) => is_null($field['type']))
            ->each(function (array $field) use (&$mainSection, &$sidebarSection) {
                $blueprintField = [
                    'handle' => $field['name'],
                    'field' => [
                        'type' => $field['type'],
                        'display' => (string) Str::of($field['name'])->headline(),
                    ],
                ];

                if (! $field['nullable']) {
                    $blueprintField['field']['validate'] = 'required';
                }

                if (in_array($field['name'], ['slug'])) {
                    $sidebarSection[] = $blueprintField;

                    return;
                }

                $mainSection[] = $blueprintField;
            });

        return \Statamic\Facades\Blueprint::make($resource->handle())
            ->setNamespace('runway')
            ->setContents([
                'tabs' => [
                    'main' => ['fields' => $mainSection],
                    'sidebar' => ['fields' => $sidebarSection],
                ],
            ])
            ->save();
    }

    protected static function getMatchingFieldtype(array $column): ?string
    {
        $mapping = Arr::get(static::$columnMappings, $column['type_name']);

        if (! $mapping) {
            return null;
        }

        if (Arr::get($column, 'name') === 'slug') {
            return 'slug';
        }

        if (Arr::get($column, 'type') === 'tinyint(1)') {
            return 'toggle';
        }

        return $mapping;
    }
}
