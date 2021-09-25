<?php

namespace DoubleThreeDigital\Runway\GraphQL;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Statamic\Facades\GraphQL;
use Rebing\GraphQL\Support\Type;
use Illuminate\Support\Str;
use Statamic\Fields\Field;

class ResourceType extends Type
{
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
        $this->attributes['name'] = "runway.graphql.types.{$resource->handle()}";
    }

    public function fields(): array
    {
        return $this->resource->blueprint()->fields()->toGql()
            ->merge($this->nonBlueprintFields())
            ->mapWithKeys(function ($value, $key) {
                return [
                    Str::replace('_id', '', $key) => $value
                ];
            })
            ->map(function ($arr) {
                if (is_array($arr)) {
                    $arr['resolve'] = $arr['resolve'] ?? $this->resolver();
                }

                return $arr;
            })
            ->all();
    }

    protected function resolver()
    {
        return function ($model, $args, $context, ResolveInfo $info) {
            if (! $model instanceof Model) {
                $resource = Runway::findResource(Str::replace('runway.graphql.types.', '', $info->parentType->name));

                $model = $resource->model()->firstWhere($resource->primaryKey(), $model);
            }

            return $model->{$info->fieldName};
        };
    }

    protected function nonBlueprintFields(): array
    {
        $columns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableColumns($this->resource->databaseTable());

        return collect($columns)
            ->reject(function ($column) {
                return in_array(
                    $column->getName(),
                    $this->resource->blueprint()->fields()->all()->keys()->toArray()
                );
            })
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
            ->reject(function ($item) {
                return is_null($item['type']);
            })
            ->toArray();
    }
}
