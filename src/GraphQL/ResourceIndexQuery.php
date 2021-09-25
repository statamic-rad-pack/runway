<?php

namespace DoubleThreeDigital\Runway\GraphQL;

use DoubleThreeDigital\Runway\Resource;
use GraphQL\Type\Definition\Type;
use Statamic\Support\Arr;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;
use Illuminate\Support\Str;
use Statamic\GraphQL\Types\JsonArgument;

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
            'filter' => GraphQL::type(JsonArgument::NAME),
            'sort' => GraphQL::listOf(GraphQL::string()),
        ];
    }

    public function resolve($root, $args)
    {
        $query = $this->resource->model();

        $query = $this->filterQuery($query, $args['filter'] ?? []);
        $query = $this->sortQuery($query, $args['sort'] ?? []);

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

            // ray($definitions);

            foreach ($definitions as $definition) {
                $condition = array_keys($definition)[0];
                $value = array_values($definition)[0];

                // Statamic has a `QueriesConditions` class where it does this stuff
                // but since we need to query slighly differently, we've just done
                // it ourselves.

                // If you need any of the conditions I've missed out, please PR
                // them or open an issue.
                switch ($condition) {
                    case 'equals':
                        $query = $query->where($field, $value);
                        break;

                    case 'null':
                        $query = $query->where($field, null);
                        break;

                    case 'isset':
                        $query = $query->where($field, '!==', null);
                        break;

                    case 'contains':
                        $query = $query->where($field, 'LIKE', "%{$value}%");
                        break;

                    case 'doesnt_contain':
                        // TODO
                        break;

                    case 'in':
                        $query = $query->whereIn($field, $value);
                        break;

                    case 'not_in':
                        $query = $query->whereNotIn($field, $value);
                        break;

                    // doesnt_begin_with
                    // ends_with
                    // doesnt_end_with
                    // greater_than

                    case 'gt':
                        $query = $query->where($field, '>', $value);
                        break;

                    // less_than

                    case 'lt':
                        $query = $query->where($field, '<', $value);
                        break;

                    // greater_than_or_equal_to

                    case 'gte':
                        $query = $query->where($field, '>=', $value);
                        break;

                    // less_than_or_equal_to

                    case 'lte':
                        $query = $query->where($field, '<=', $value);
                        break;

                    // matches
                    // match

                    case 'regex':
                        //
                        break;

                    case 'doesnt_match':
                        $query = $query->where($field, '!=', $value);
                        break;

                    // is_alpha
                    // is_alpha_numeric
                    // is_numeric
                    // is_url
                    // is_embeddable
                    // is_email
                    // is_after
                    // is_future
                    // is_before
                    // is_past
                    // is_numberwang

                    default:
                        # code...
                        break;
                }
            }
        }

        return $query;
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

        return $query;
    }
}
