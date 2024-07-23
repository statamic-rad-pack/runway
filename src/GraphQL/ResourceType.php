<?php

namespace StatamicRadPack\Runway\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Type;
use Statamic\Facades\GraphQL;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

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
            ->merge($this->nestedFields())
            ->when($this->resource->hasPublishStates(), function ($collection) {
                $collection->put('status', ['type' => GraphQL::nonNull(GraphQL::string())]);
                $collection->put('published', ['type' => GraphQL::nonNull(GraphQL::boolean())]);
            })
            ->reject(fn ($value, $key) => $this->resource->nestedFieldPrefix($key))
            ->mapWithKeys(fn ($value, $key) => [
                Str::replace('_id', '', $key) => $value,
            ])
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
        return collect(Schema::getColumns($this->resource->databaseTable()))
            ->reject(fn (array $column) => in_array(
                $column['name'],
                $this->resource->blueprint()->fields()->all()->keys()->toArray()
            ))
            ->mapWithKeys(function (array $column): array {
                $type = null;

                if ($column['type_name'] === 'bigint') {
                    $type = GraphQL::int();
                }

                if ($column['type_name'] === 'varchar' || $column['type_name'] === 'string') {
                    $type = GraphQL::string();
                }

                if ($column['type_name'] === 'timestamp' || $column['type_name'] === 'datetime') {
                    $type = GraphQL::string();
                }

                if ($column['nullable'] === false && ! is_null($type)) {
                    $type = GraphQL::nonNull($type);
                }

                return [$column['name'] => ['type' => $type]];
            })
            ->reject(fn ($item): bool => is_null($item['type']))
            ->all();
    }

    protected function nestedFields(): array
    {
        return $this->resource->nestedFieldPrefixes()->mapWithKeys(fn (string $nestedFieldPrefix) => [
            $nestedFieldPrefix => ['type' => GraphQL::type(NestedFieldsType::buildName($this->resource, $nestedFieldPrefix))],
        ])->all();
    }
}
