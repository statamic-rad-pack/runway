<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

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
        ];

        return array_merge($config, parent::configFieldItems());
    }
}
