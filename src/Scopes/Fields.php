<?php

namespace StatamicRadPack\Runway\Scopes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Fields\Field;
use Statamic\Query\Scopes\Filters\Fields as BaseFieldsFilter;
use StatamicRadPack\Runway\Runway;

class Fields extends BaseFieldsFilter
{
    protected static $handle = 'runway-fields';

    public function visibleTo($key): bool
    {
        return $key === 'runway';
    }

    public function apply($query, $values): void
    {
        $this->getFields()
            ->filter(fn (Field $field, string $handle) => Arr::has($values, $handle))
            ->each(function (Field $field, string $handle) use ($query, $values) {
                $filter = $field->fieldtype()->filter();
                $values = $filter->fields()->addValues($values[$handle])->process()->values();
                $filter->apply($query, $handle, $values);
            });
    }

    protected function getFields(): Collection
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
}
