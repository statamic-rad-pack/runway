<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;
use GraphQL\Type\Definition\ResolveInfo;
use Statamic\Facades\GraphQL;

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
        ];

        return array_merge($config, parent::configFieldItems());
    }

    // Pre-process the data before it gets sent to the publish page
    public function preProcess($data)
    {
        return collect($data)
            ->pluck('id')
            ->toArray();
    }

    // Process the data before it gets saved
    public function process($data)
    {
        $resource = Runway::findResource(request()->route('resourceHandle'));
        $record = $resource->model()->firstWhere($resource->routeKey(), (int) request()->route('record'));

        // If we're adding HasMany relations on a model that doesn't exist yet,
        // return a closure that will be run post-save.
        if (! $record) {
            return function ($resource, $record) use ($data) {
                $relatedResource = Runway::findResource($this->config('resource'));
                $relatedField = $record->{$this->field()->handle()}();

                $relatedField
                    ->each(function ($model) use ($relatedField) {
                        $model->update([
                            $relatedField->getForeignKeyName() => null,
                        ]);
                    });

                collect($data)
                    ->each(function ($relatedId) use ($record, $relatedResource, $relatedField) {
                        $relatedResource->model()->find($relatedId)->update([
                            $relatedField->getForeignKeyName() => $record->id,
                        ]);
                    });
            };
        }

        $relatedResource = Runway::findResource($this->config('resource'));
        $relatedField = $record->{$this->field()->handle()}();

        // I don't understand this code, why are you updating the reference? The `id` won't change.
        $relatedField
            ->each(function ($model) use ($record, $relatedField) {
                $model->update([
                    $relatedField->getForeignKeyName() => $record->id,
                ]);
            });

        // and why update it again???
        collect($data)
            ->each(function ($relatedId) use ($record, $relatedResource, $relatedField) {
                $relatedResource->model()->find($relatedId)->update([
                    $relatedField->getForeignKeyName() => $record->id,
                ]);
            });

        return null;
    }

    public function toGqlType()
    {
        $resource = Runway::findResource($this->config('resource'));

        return [
            'type' => GraphQL::listOf(GraphQL::type("runway.graphql.types.{$resource->handle()}")),
            'resolve' => function ($model, $args, $context, ResolveInfo $info) use ($resource) {
                return $model->{$info->fieldName};
            },
        ];
    }
}
