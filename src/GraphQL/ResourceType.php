<?php

namespace StatamicRadPack\Runway\GraphQL;

use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Type;
use Statamic\Facades\GraphQL;

class ResourceType extends Type
{
    public function __construct(protected Resource $resource)
    {
        $this->attributes['name'] = "runway_graphql_types_{$this->resource->handle()}";
    }

    public function fields(): array
    {
        return $this->resource->blueprint()->fields()->toGql()
            ->merge($this->nonBlueprintFields())
            ->mapWithKeys(fn ($value, $key) => [
                Str::replace('_id', '', $key) => $value,
            ])
            // TODO: Make nested fields work with GraphQL
            ->reject(fn ($value, $key) => str_contains($key, '->'))
            ->map(function ($arr) {
                if (is_array($arr)) {
                    $arr['resolve'] ??= $this->resolver();
                }

                return $arr;
            })
            ->all();
    }

    protected function resolver()
    {
        return function ($model, $args, $context, ResolveInfo $info) {
            if (! $model instanceof Model) {
                $resource = Runway::findResource(Str::replace('runway_graphql_types_', '', $info->parentType->name));

                $model = $resource->model()->firstWhere($resource->primaryKey(), $model);
            }

            return $model->resolveGqlValue($info->fieldName);
        };
    }

    protected function nonBlueprintFields(): array
    {
        $columns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableColumns($this->resource->databaseTable());

        return collect($columns)
            ->reject(fn ($column) => in_array(
                $column->getName(),
                $this->resource->blueprint()->fields()->all()->keys()->toArray()
            ))
            ->map(function ($column) {
                $type = null;

                if ($column->getType() instanceof \Doctrine\DBAL\Types\BigIntType) {
                    $type = GraphQL::int();
                }

                if ($column->getType() instanceof \Doctrine\DBAL\Types\StringType) {
                    $type = GraphQL::string();
                }

                if ($column->getType() instanceof \Doctrine\DBAL\Types\DateTimeType) {
                    $type = GraphQL::string();
                }

                if ($column->getNotnull() === true && ! is_null($type)) {
                    $type = GraphQL::nonNull($type);
                }

                return [
                    'type' => $type,
                ];
            })
            ->reject(fn ($item) => is_null($item['type']))
            ->toArray();
    }
}
