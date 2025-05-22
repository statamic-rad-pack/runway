<?php

namespace StatamicRadPack\Runway\GraphQL;

use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Queries\Query;
use Statamic\GraphQL\Types\JsonArgument;
use StatamicRadPack\Runway\Resource;

class ResourceIndexQuery extends Query
{
    use FiltersQuery {
        filterQuery as traitFilterQuery;
    }

    public function __construct(protected Resource $resource)
    {
        $this->attributes['name'] = Str::lower($this->resource->plural());
    }

    public function type(): Type
    {
        return GraphQL::paginate(GraphQL::type("runway_graphql_types_{$this->resource->handle()}"));
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
        $query = $this->resource->newEloquentQuery()->with($this->resource->eagerLoadingRelationships());

        $this->filterQuery($query, $args['filter'] ?? []);
        $this->sortQuery($query, $args['sort'] ?? []);

        return $query->paginate(
            $args['limit'] ?? null,
            ['*'],
            'page',
            $args['page'] ?? null
        );
    }

    private function filterQuery($query, $filters)
    {
        if (! isset($filters['status']) && ! isset($filters['published'])) {
            $filters['status'] = 'published';
        }

        $this->traitFilterQuery($query, $filters);
    }

    protected function sortQuery($query, $sorts): void
    {
        if (empty($sorts)) {
            $sorts = ['id'];
        }

        foreach ($sorts as $sort) {
            $order = 'asc';

            if (Str::contains($sort, ' ')) {
                [$sort, $order] = explode(' ', (string) $sort);
            }

            $query = $query->orderBy($sort, $order);
        }
    }
}
