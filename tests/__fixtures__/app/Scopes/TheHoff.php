<?php

namespace StatamicRadPack\Runway\Tests\Fixtures\Scopes;

use Statamic\Query\Builder;
use Statamic\Query\Scopes\Scope;

class TheHoff extends Scope
{
    /**
     * Apply the scope.
     *
     * @param  Builder  $query
     * @param  array  $values
     * @return void
     */
    public function apply($query, $values)
    {
        $query->where('name', 'like', '%Hasselhoff%');
    }
}
