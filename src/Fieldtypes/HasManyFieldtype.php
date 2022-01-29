<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Request;
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
        ];

        return array_merge($config, parent::configFieldItems());
    }

    // Pre-process the data before it gets sent to the publish page
    public function preProcess($data)
    {
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
        $resourceHandle = request()->route('resourceHandle');

        if (! $resourceHandle) {
            return $data;
        }

        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->firstWhere($resource->routeKey(), (int) Request::route('record'));

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
            ->reject(function ($model) use ($data) {
                return in_array($model->id, $data);
            })
            ->each(function ($model) use ($relatedResource, &$deleted) {
                $deleted[] = $model->{$relatedResource->primaryKey()};

                $model->delete();
            });

        // Add anything new
        collect($data)
            ->reject(function ($relatedId) use ($relatedResource, $relatedField) {
                return $relatedField->get()->pluck($relatedResource->primaryKey())->contains($relatedId);
            })
            ->reject(function ($relatedId) use ($deleted) {
                return in_array($relatedId, $deleted);
            })
            ->each(function ($relatedId) use ($record, $relatedResource, $relatedField) {
                $model = $relatedResource->model()->find($relatedId);

                $model->update([
                    $relatedField->getForeignKeyName() => $record->{$relatedResource->primaryKey()},
                ]);
            });

        return null;
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            'type' => GraphQL::listOf(GraphQL::type("runway.graphql.types.{$resource->handle()}")),
            'resolve' => function ($model, $args, $context, ResolveInfo $info) {
                return $model->{$info->fieldName};
            },
        ];
    }
}
