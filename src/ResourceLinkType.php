<?php

namespace StatamicRadPack\Runway;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link\LinkType;
use Statamic\Support\Str;

class ResourceLinkType extends LinkType
{
    const PREFIX = 'runway_';

    public function title(): string
    {
        return $this->resource()->singular();
    }

    public function icon(): string
    {
        return $this->resource()->icon();
    }

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        $resource = $this->resource();

        return $resource->newEloquentQuery()
            ->when(
                $resource->model()->hasNamedScope('runwayLinkFieldtype'),
                fn ($query) => $query->runwayLinkFieldtype()
            )
            ->firstWhere($resource->qualifiedPrimaryKey(), $id);
    }

    public function fieldtype(Field $field): ?array
    {
        return [
            'type' => 'belongs_to',
            'resource' => $this->resource()->handle(),
            'max_items' => 1,
            'mode' => 'default',
        ];
    }

    private function resource(): Resource
    {
        return Runway::findResource(Str::after($this->handle(), self::PREFIX));
    }
}
