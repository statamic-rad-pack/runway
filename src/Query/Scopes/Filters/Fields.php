<?php

namespace DoubleThreeDigital\Runway\Query\Scopes\Filters;

use DoubleThreeDigital\Runway\Runway;
use Statamic\Fields\Field;
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

        return $resource->blueprint()->fields()->all()
            ->filter->isFilterable()
            ->reject(function (Field $field) use ($resource) {
                return in_array($field->handle(), $resource->model()->getAppends(), true)
                    && ! $resource->model()->hasSetMutator($field->handle())
                    && ! $resource->model()->hasAttributeSetMutator($field->handle());
            });
    }

    public function apply($query, $values)
    {
        $this->getFields()
            ->filter(function ($field, $handle) use ($values) {
                return isset($values[$handle]);
            })
            ->each(function ($field, $handle) use ($query, $values) {
                $filter = $field->fieldtype()->filter();
                $values = $filter->fields()->addValues($values[$handle])->process()->values();
                $filter->apply($query, $handle, $values);
            });
    }
}
