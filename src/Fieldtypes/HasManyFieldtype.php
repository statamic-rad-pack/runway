<?php

namespace StatamicRadPack\Runway\Fieldtypes;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Statamic\Facades\GraphQL;
use StatamicRadPack\Runway\Runway;

use function Statamic\trans as __;

class HasManyFieldtype extends BaseFieldtype
{
    protected $indexComponent = 'relationship';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    ...parent::configFieldItems()[0]['fields'],
                    'reorderable' => [
                        'display' => __('Reorderable?'),
                        'instructions' => __('runway::fieldtypes.has_many.config.reorderable'),
                        'type' => 'toggle',
                        'width' => 50,
                    ],
                    'order_column' => [
                        'display' => __('Order Column'),
                        'instructions' => __('runway::fieldtypes.has_many.config.order_column'),
                        'type' => 'text',
                        'width' => 50,
                        'placeholder' => 'sort_order',
                        'if' => [
                            'reorderable' => true,
                        ],
                    ],
                ],
            ],
            parent::configFieldItems()[1],
            [
                'display' => __('Boundaries & Limits'),
                'fields' => [
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                    ],
                ],
            ],
        ];

        $config = [
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'type' => 'integer',
                'width' => 50,
            ],
            'reorderable' => [
                'display' => __('Reorderable?'),
                'instructions' => __('runway::fieldtypes.has_many.config.reorderable'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'order_column' => [
                'display' => __('Order Column'),
                'instructions' => __('runway::fieldtypes.has_many.config.order_column'),
                'type' => 'text',
                'width' => 50,
                'placeholder' => 'sort_order',
                'if' => [
                    'reorderable' => true,
                ],
            ],
        ];

        return array_merge(parent::configFieldItems(), $config);
    }

    /**
     * Pre-process the values before they get sent to the publish form.
     *
     * @return array
     */
    public function preProcess($data)
    {
        $resource = Runway::findResource($this->config('resource'));

        if (collect($data)->every(fn ($item) => $item instanceof Model)) {
            return collect($data)->pluck($resource->primaryKey())->all();
        }

        return Arr::wrap($data);
    }

    public function preload()
    {
        return array_merge(parent::preload(), [
            'actionUrl' => cp_route('runway.models.actions.run', [
                'resource' => $this->config('resource'),
            ]),
        ]);
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            'type' => GraphQL::listOf(GraphQL::type("runway_graphql_types_{$resource->handle()}")),
            'resolve' => function ($item, $args, $context, ResolveInfo $info) {
                if (! $item instanceof Model) {
                    return $item->get($info->fieldName);
                }

                return $item->{$info->fieldName};
            },
        ];
    }
}
