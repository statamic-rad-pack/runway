<?php

namespace DoubleThreeDigital\Runway\GraphQL;

use DoubleThreeDigital\Runway\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Fields\DateField;
use Rebing\GraphQL\Support\Type;

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
        return function (Model $model, $args, $context, $info) {
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
                    // return new DateField;
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
