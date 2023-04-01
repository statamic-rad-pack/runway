<?php

namespace DuncanMcClean\Runway\GraphQL;

use DuncanMcClean\Runway\Resource;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;

class ResourceShowQuery extends Query
{
    public function __construct(protected Resource $resource)
    {
        $this->attributes['name'] = Str::lower($this->resource->singular());
    }

    public function type(): Type
    {
        return GraphQL::type("runway.graphql.types.{$this->resource->handle()}");
    }

    public function args(): array
    {
        return [
            $this->resource->primaryKey() => GraphQL::nonNull(GraphQL::id()),
        ];
    }

    public function resolve($root, $args)
    {
        return $this->resource->model()->find($args[$this->resource->primaryKey()]);
    }
}
