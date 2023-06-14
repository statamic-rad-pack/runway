<?php

namespace DoubleThreeDigital\Runway\Query\Scopes\Filters;

use DoubleThreeDigital\Runway\Runway;
use Statamic\Query\Scopes\Filters\Fields as BaseFieldsFilter;

class Fields extends BaseFieldsFilter
{
    protected static $handle = 'runway-fields';

    public function visibleTo($key)
    {
        return $key === 'runway';
    }

    protected function getFields()
    {
        $resource = Runway::findResource($this->context['resource']);

        return $resource->blueprint()->fields()->all()->filter->isFilterable();
    }
}
