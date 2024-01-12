<?php

namespace StatamicRadPack\Runway\GraphQL;

use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;
use StatamicRadPack\Runway\Resource;

class ResourceShowQuery extends Query
{
    public function __construct(protected Resource $resource)
    {
        $this->attributes['name'] = Str::lower($this->resource->singular());
    }

    public function type(): Type
    {
        return GraphQL::type("runway_graphql_types_{$this->resource->handle()}");
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
