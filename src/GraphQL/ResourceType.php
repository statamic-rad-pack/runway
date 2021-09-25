<?php

namespace DoubleThreeDigital\Runway\GraphQL;

use DoubleThreeDigital\Runway\Resource;
use Illuminate\Database\Eloquent\Model;
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
            ->merge([
                'id' => ['type' => GraphQL::nonNull(GraphQL::int())],
                // 'created_at' => new DateField,
                // 'updated_at' => new DateField,
            ])
            ->map(function ($item) {
                return array_merge($item, [
                    'resolve' => $this->resolver(),
                ]);
            })
            ->all();
    }

    protected function resolver()
    {
        return function (Model $model, $args, $context, $info) {
            return $model->{$info->fieldName};
        };
    }
}
