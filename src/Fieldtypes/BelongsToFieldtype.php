<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

class BelongsToFieldtype extends BaseFieldtype
{
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $formComponent = 'runway-publish-form';

    protected $formComponentProps = [
        'initialBlueprint' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'initialTitle' => 'title',
        'action' => 'action',
        'method' => 'method',
        'resourceHasRoutes' => 'resourceHasRoutes',
        'permalink' => 'permalink',
    ];

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
