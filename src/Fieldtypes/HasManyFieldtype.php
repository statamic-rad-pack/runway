<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Statamic\Facades\Blink;
use Statamic\Facades\GraphQL;

class HasManyFieldtype extends BaseFieldtype
{
    protected $itemComponent = 'hasmany-related-item';

    protected function configFieldItems(): array
    {
        $config = [
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'type' => 'integer',
                'width' => 50,
            ],
            'title_format' => [
                'display' => __('Title Format'),
                'instructions' => __('Configure a title format for results. You should use Antlers to pull in field data.'),
                'type' => 'text',
                'width' => 50,
            ],
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                'type' => 'radio',
                'default' => 'default',
                'options' => [
                    'default' => __('Stack Selector'),
                    'select' => __('Select Dropdown'),
                    'typeahead' => __('Typeahead Field'),
                    'table' => __('Table'),
                ],
                'width' => 50,
            ],
            'reorderable' => [
                'display' => __('Reorderable?'),
                'instructions' => __('Can the models be reordered?'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'order_column' => [
                'display' => __('Order Column'),
                'instructions' => __('Which column should be used to keep track of the order?'),
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

    // Pre-process the data before it gets sent to the publish page
    public function preProcess($data)
    {
        // Determine whether or not this field is on a resource or a collection
        $resourceHandle = request()->route('resourceHandle');

        if (! $resourceHandle) {
            return $data;
        }

        return collect($data)
            ->pluck('id')
            ->toArray();
    }

    // Process the data before it gets saved
    public function process($data)
    {
        // Determine whether or not this field is on a resource or a collection
        $resourceHandle = request()->route('resourceHandle') ?? Blink::get('RunwayRouteResource');

        if (! $resourceHandle) {
            return $data;
        }

        $resource = Runway::findResource($resourceHandle);

        $record = $resource->model()->firstWhere(
            $resource->routeKey(),
            request()->route('record') ?? Blink::get('RunwayRouteRecord')
        );

        // If we're adding HasMany relations on a model that doesn't exist yet,
        // return a closure that will be run post-save.
        if (! $record) {
            return function ($resource, $record) use ($data) {
                $relatedResource = Runway::findResource($this->config('resource'));
                $relatedField = $record->{$this->field()->handle()}();

                // Many to many relation
                if ($relatedField instanceof BelongsToMany) {
                    $record->{$this->field()->handle()}()->sync($data);
                } else {
                    // Add anything new
                    collect($data)
                        ->each(function ($relatedId) use ($record, $relatedResource, $relatedField) {
                            $model = $relatedResource->model()->find($relatedId);

                            $model->update([
                                $relatedField->getForeignKeyName() => $record->{$relatedResource->primaryKey()},
                            ]);
                        });
                }
            };
        }

        $deleted = [];
        $relatedResource = Runway::findResource($this->config('resource'));
        $relatedField = $record->{$this->field()->handle()}();

        // Many to many relation
        if ($relatedField instanceof BelongsToMany) {
            $record->{$this->field()->handle()}()->sync($data);

            return null;
        }

        // Delete any deleted models
        collect($relatedField->get())
            ->reject(fn ($model) => in_array($model->id, $data))
            ->each(function ($model) use ($relatedResource, &$deleted) {
                $deleted[] = $model->{$relatedResource->primaryKey()};

                $model->delete();
            });

        // Add anything new
        collect($data)
            ->reject(fn ($relatedId) => $relatedField->get()->pluck($relatedResource->primaryKey())->contains($relatedId))
            ->reject(fn ($relatedId) => in_array($relatedId, $deleted))
            ->each(function ($relatedId) use ($record, $relatedResource, $relatedField) {
                $model = $relatedResource->model()->find($relatedId);

                $model->update([
                    $relatedField->getForeignKeyName() => $record->{$relatedResource->primaryKey()},
                ]);
            });

        // If reordering is enabled, update all models with their new sort order.
        if ($this->config('reorderable') && $orderColumn = $this->config('order_column')) {
            collect($data)
                ->each(function ($relatedId, $index) use ($relatedResource, $orderColumn) {
                    $model = $relatedResource->model()->find($relatedId);

                    if ($model->{$orderColumn} === $index) {
                        return;
                    }

                    $model->update([
                        $orderColumn => $index,
                    ]);
                });
        }

        return null;
    }

    public function preload()
    {
        return array_merge(parent::preload(), [
            'actionUrl' => cp_route('runway.actions.run', [
                'resourceHandle' => $this->config('resource'),
            ]),
        ]);
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            'type' => GraphQL::listOf(GraphQL::type("runway.graphql.types.{$resource->handle()}")),
            'resolve' => fn ($model, $args, $context, ResolveInfo $info) => $model->{$info->fieldName},
        ];
    }
}
