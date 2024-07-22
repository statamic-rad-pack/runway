<?php

namespace StatamicRadPack\Runway\GraphQL;

use Statamic\Contracts\GraphQL\ResolvesValues;
use Statamic\Fields\Field;
use Statamic\Support\Str;
use StatamicRadPack\Runway\Resource;

class NestedFieldsType extends \Rebing\GraphQL\Support\Type
{
    public function __construct(protected Resource $resource, protected string $nestedFieldPrefix)
    {
        $this->attributes['name'] = static::buildName($resource, $nestedFieldPrefix);
    }

    public static function buildName(Resource $resource, string $nestedFieldPrefix): string
    {
        return 'Runway_NestedFields_'.Str::studly($resource->handle()).'_'.Str::studly($nestedFieldPrefix);
    }

    public function fields(): array
    {
        return $this->resource->blueprint()->fields()->all()
            ->filter(fn (Field $field) => Str::startsWith($field->handle(), $this->nestedFieldPrefix))
            ->mapWithKeys(function (Field $field) {
                $field = (clone $field);
                $handle = Str::after($field->handle(), "{$this->nestedFieldPrefix}_");

                return [$handle => $field->setHandle($handle)];
            })
            ->map->toGql()
            ->all();
    }
}
