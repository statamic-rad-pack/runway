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
        $config = [
            'max_items' => [
                'display' => __('Max Items'),
                'type' => 'hidden',
                'width' => 50,
                'default' => 1,
                'read_only' => true,
            ],
            'title_format' => [
                'display' => __('Title Format'),
                'instructions' => __('Configure a title format for results. You should use Antlers to pull in field data.'),
                'type' => 'text',
                'width' => 50,
            ],
            'relationship_name' => [
                'display' => __('Relationship Name'),
                'instructions' => __('The name of the Eloquent relationship this field should use. When left blank, Runway will attempt to guess it.'),
                'type' => 'text',
                'width' => 50,
            ],
        ];

        return array_merge($config, parent::configFieldItems());
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            'type' => GraphQL::type("runway_graphql_types_{$resource->handle()}"),
            'resolve' => function ($item, $args, $context, ResolveInfo $info) use ($resource) {
                if (! $item instanceof Model) {
                    return $item->get($info->fieldName);
                }

                return $item->{$info->fieldName};
            },
        ];
    }
}
