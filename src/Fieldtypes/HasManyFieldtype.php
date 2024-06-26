<?php

namespace StatamicRadPack\Runway\Fieldtypes;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Statamic\Facades\Blink;
use Statamic\Facades\GraphQL;
use StatamicRadPack\Runway\Runway;

class HasManyFieldtype extends BaseFieldtype
{
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
        $resource = Runway::findResource($this->config('resource'));

        // Determine whether or not this field is on a resource or a collection
        $resourceHandle = request()->route('resource');

        if (! $resourceHandle) {
            return Arr::wrap($data);
        }

        return collect($data)
            ->pluck($resource->primaryKey())
            ->toArray();
    }

    // Process the data before it gets saved
    public function process($data)
    {
        // Determine whether or not this field is on a resource or a collection
        $resource = request()->route('resource');

        if (Blink::get('RunwayRouteResource')) {
            $resource = Runway::findResource(Blink::get('RunwayRouteResource'));
        }

        if (! $resource) {
            return $data;
        }

        $model = $resource->model()->firstWhere(
            $resource->routeKey(),
            request()->route('model') ?? Blink::get('RunwayRouteModel')
        );

        // If we're adding HasMany relations on a model that doesn't exist yet,
        // return a closure that will be run post-save.
        if (! $model) {
            return function ($resource, $model) use ($data) {
                $relatedResource = Runway::findResource($this->config('resource'));
                $relatedField = $model->{$this->field()->handle()}();

                // Many to many relation
                if ($relatedField instanceof BelongsToMany) {
                    $model->{$this->field()->handle()}()->sync($data);
                } else {
                    // Add anything new
                    collect($data)
                        ->each(function ($relatedId) use ($model, $relatedResource, $relatedField) {
                            $relatedModel = $relatedResource->model()->find($relatedId);

                            $update = [
                                $relatedField->getForeignKeyName() => $model->{$relatedResource->primaryKey()},
                            ];
            
                            if ($relatedField instanceof MorphMany) {
                                $update[$relatedField->getMorphType()] = $relatedField->getMorphClass();
                            }
            
                            $relatedModel->update($update);
                        });
                }
            };
        }

        $deleted = [];
        $relatedResource = Runway::findResource($this->config('resource'));
        $relatedField = $model->{$this->field()->handle()}();

        // Many to many relation
        if ($relatedField instanceof BelongsToMany) {
            // When Reordering is enabled, we need to change the format of the $data array. The key should
            // be the foriegn key and the value should be pivot data (our sort order).
            if ($this->config('reorderable') && $orderColumn = $this->config('order_column')) {
                $data = collect($data)
                    ->mapWithKeys(function ($relatedId, $index) use ($orderColumn) {
                        return [$relatedId => [$orderColumn => $index]];
                    })
                    ->toArray();
            }

            $model->{$this->field()->handle()}()->sync($data);

            return null;
        }

        // Delete any deleted models
        collect($relatedField->get())
            ->reject(fn ($relatedModel) => in_array($relatedModel->id, $data))
            ->each(function ($relatedModel) use ($relatedResource, &$deleted) {
                $deleted[] = $relatedModel->{$relatedResource->primaryKey()};

                $relatedModel->delete();
            });

        // Add anything new
        collect($data)
            ->reject(fn ($relatedId) => $relatedField->get()->pluck($relatedResource->primaryKey())->contains($relatedId))
            ->reject(fn ($relatedId) => in_array($relatedId, $deleted))
            ->each(function ($relatedId) use ($model, $relatedResource, $relatedField) {
                $relatedModel = $relatedResource->model()->find($relatedId);

                $update = [
                    $relatedField->getForeignKeyName() => $model->{$relatedResource->primaryKey()},
                ];

                if ($relatedField instanceof MorphMany) {
                    $update[$relatedField->getMorphType()] = $relatedField->getMorphClass();
                }

                $relatedModel->update($update);
            });

        // If reordering is enabled, update all models with their new sort order.
        if ($this->config('reorderable') && $orderColumn = $this->config('order_column')) {
            collect($data)
                ->each(function ($relatedId, $index) use ($relatedResource, $orderColumn) {
                    $relatedModel = $relatedResource->model()->find($relatedId);

                    if ($relatedModel->{$orderColumn} === $index) {
                        return;
                    }

                    $relatedModel->update([
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
                'resource' => $this->config('resource'),
            ]),
        ]);
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            'type' => GraphQL::listOf(GraphQL::type("runway_graphql_types_{$resource->handle()}")),
            'resolve' => fn ($model, $args, $context, ResolveInfo $info) => $model->{$info->fieldName},
        ];
    }
}
