<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;
use Statamic\Facades\GraphQL;

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

        return GraphQL::type("runway_graphql_types_{$resource->handle()}");
    }
}
