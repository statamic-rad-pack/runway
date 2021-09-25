<?php

namespace DoubleThreeDigital\Runway\GraphQL;

use DoubleThreeDigital\Runway\Resource;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;
use Illuminate\Support\Str;

class ResourceIndexQuery extends Query
{
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
        $this->attributes['name'] = Str::lower($resource->plural());
    }

    public function type(): Type
    {
        return GraphQL::paginate(GraphQL::type("runway.graphql.types.{$this->resource->handle()}"));
    }

    public function args(): array
    {
        return [
            'limit' => GraphQL::int(),
            'page' => GraphQL::int(),
        ];
    }

    public function resolve($root, $args)
    {
        return $this->resource->model()->paginate(
            $args['limit'] ?? null,
            ['*'],
            'page',
            $args['page'] ?? null
        );
    }
}
