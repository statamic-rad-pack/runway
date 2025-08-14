<?php

namespace StatamicRadPack\Runway\Scopes;

use Illuminate\Support\Collection;
use Statamic\Fields\Field;
use Statamic\Query\Scopes\Filters\Fields as BaseFieldsFilter;
use StatamicRadPack\Runway\Runway;
use Statamic\Support\Arr;

class Fields extends BaseFieldsFilter
{
    protected static $handle = 'runway_fields';

    public function visibleTo($key): bool
    {
        return $key === 'runway';
    }

    public function apply($query, $values): void
    {
        $resource = Runway::findResource($this->context['resource']);

        $this->getFields()
            ->filter(function ($field, $handle) use ($values) {
                return isset($values[$handle]) && $this->isComplete($values[$handle]);
            })
            ->each(function (Field $field) use ($query, $values, $resource) {
                $filter = $field->fieldtype()->filter();
                $values = $filter->fields()->addValues($values[$field->handle()])->process()->values();

                $filter->apply($query, $resource->model()->getColumnForField($field->handle()), $values);
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

    private function isComplete(array $values): bool
    {
        $values = array_filter($values);

        return Arr::has($values, 'operator') && Arr::has($values, 'value');
    }
}
