<?php

namespace StatamicRadPack\Runway\Fieldtypes;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\GraphQL;
use StatamicRadPack\Runway\Runway;

class BelongsToFieldtype extends BaseFieldtype
{
    protected function configFieldItems(): array
    {
        return [
            ...parent::configFieldItems(),
            [
                'fields' => [
                    'max_items' => [
                        'type' => 'integer',
                        'default' => 1,
                        'visibility' => 'hidden',
                    ],
                ],
            ],
        ];
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            'type' => GraphQL::type("runway_graphql_types_{$resource->handle()}"),
            'resolve' => function ($item, $args, $context, ResolveInfo $info) {
                if (! $item instanceof Model) {
                    return $item->get($info->fieldName);
                }

                return $item->{$info->fieldName};
            },
        ];
    }
}
