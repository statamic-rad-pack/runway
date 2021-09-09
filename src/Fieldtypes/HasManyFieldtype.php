<?php

namespace DoubleThreeDigital\Runway\Fieldtypes;

use DoubleThreeDigital\Runway\Runway;

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

        return null;
    }
}
