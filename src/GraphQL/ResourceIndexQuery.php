<?php

namespace DoubleThreeDigital\Runway\GraphQL;

use DoubleThreeDigital\Runway\Resource;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Arr;
use Statamic\Tags\Concerns\QueriesConditions;

class ResourceIndexQuery extends Query
{
    use QueriesConditions;

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
            'filter' => GraphQL::type(JsonArgument::NAME),
            'sort' => GraphQL::listOf(GraphQL::string()),
        ];
    }

    public function resolve($root, $args)
    {
        $query = $this->resource->model()->newQuery();

        $this->filterQuery($query, $args['filter'] ?? []);
        $this->sortQuery($query, $args['sort'] ?? []);

        return $query->paginate(
            $args['limit'] ?? null,
            ['*'],
            'page',
            $args['page'] ?? null
        );
    }

    protected function filterQuery($query, $filters)
    {
        foreach ($filters as $field => $definitions) {
            if (! is_array($definitions)) {
                $definitions = [['equals' => $definitions]];
            }

            if (Arr::assoc($definitions)) {
                $definitions = collect($definitions)->map(function ($value, $key) {
                    return [$key => $value];
                })->values()->all();
            }

            foreach ($definitions as $definition) {
                $condition = array_keys($definition)[0];
                $value = array_values($definition)[0];

                $this->queryCondition($query, $field, $condition, $value);
            }
        }
    }

    protected function sortQuery($query, $sorts)
    {
        if (empty($sorts)) {
            $sorts = ['id'];
        }

        foreach ($sorts as $sort) {
            $order = 'asc';

            if (Str::contains($sort, ' ')) {
                [$sort, $order] = explode(' ', $sort);
            }

            $query = $query->orderBy($sort, $order);
        }
    }
}
