<?php

namespace StatamicRadPack\Runway\Search;

class SearchablePolicy
{
    public function view($user, Searchable $searchable)
    {
        return $user->can('view', [$searchable->resource(), $searchable->model()]);
    }
}
