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
                'type' => 'text',
                'width' => 50,
            ],
        ];

        return array_merge($config, parent::configFieldItems());
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return GraphQL::type("runway.graphql.types.{$resource->handle()}");
    }
}
