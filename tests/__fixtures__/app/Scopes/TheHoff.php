<?php

namespace StatamicRadPack\Runway\Tests\Fixtures\Scopes;

use Statamic\Query\Scopes\Scope;

class TheHoff extends Scope
{
    /**
     * Apply the scope.
     *
     * @param  \Statamic\Query\Builder  $query
     * @param  array  $values
     * @return void
     */
    public function apply($query, $values)
    {
        $query->where('name', 'like', '%Hasselhoff%');
    }
}
