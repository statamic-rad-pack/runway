<?php

namespace DoubleThreeDigital\Runway\GraphQL;

use DoubleThreeDigital\Runway\Resource;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;
use Illuminate\Support\Str;

class ResourceShowQuery extends Query
{
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
        $this->attributes['name'] = Str::lower($resource->singular());
    }

    public function type(): Type
    {
        return GraphQL::type("runway.graphql.types.{$this->resource->handle()}");
    }

    public function args(): array
    {
        return [
            'id' => GraphQL::nonNull(GraphQL::int()),
        ];
    }

    public function resolve($root, $args)
    {
        // TODO: make work with different foreign keys
        return $this->resource->model()->find($args['id']);
    }
}
