<?php

namespace DoubleThreeDigital\Runway\Data;

use Carbon\CarbonInterface;
use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Support\Json;
use Illuminate\Database\Eloquent\Model;
use Statamic\Data\AbstractAugmented;
use Statamic\Fields\Blueprint;

class AugmentedModel extends AbstractAugmented
{
    protected $data;

    protected $resource;

    protected $supplements = [];

    public function __construct($model)
    {
        $this->data = $model;
        $this->resource = Runway::findResourceByModel($model);
    }

    public function supplement(array $data)
    {
        $this->supplements = $data;

        return $this;
    }

    public function keys()
    {
        return collect()
            ->merge($this->blueprintFields()->keys())
            ->merge($this->commonKeys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys()
    {
        return [
            'url',
        ];
    }

    public function url(): ?string
    {
        return $this->resource->hasRouting() ? $this->data->uri() : null;
    }

    /**
     * Takes in an Eloquent model & augments it with the fields
     * from the resource's blueprint.
     *
     * TODO: Consider if we still need this or if what this does can be done elsewhere.
     */
    public static function augment(Model $record, Blueprint $blueprint): array
    {
        $resource = Runway::findResourceByModel($record);

        $modelKeyValue = $record->toArray();

        $resourceKeyValue = $resource->blueprint()->fields()->items()->pluck('handle')
            ->mapWithKeys(function ($fieldHandle) use ($record) {
                // By using a 'dotted' key instead of arrows we can later 'undot' the
                // collection, ensuring nested fields are assigned their augmented value.
                $key = str_replace('->', '.', $fieldHandle);

                return [$key => data_get($record, $key)];
            });

        return collect($modelKeyValue)
            ->merge($resourceKeyValue)
            ->map(function ($value, $key) use ($resource, $blueprint) {
                $fieldHandle = str_replace('.', '->', $key);

                // When $value is a Carbon instance, format it with the format
                // specified in the blueprint.
                if ($value instanceof CarbonInterface) {
                    $format = $defaultFormat = 'Y-m-d H:i';

                    if ($field = $resource->blueprint()->field($fieldHandle)) {
                        $format = $field->get('format', $defaultFormat);
                    }

                    return $value->format($format);
                }

                // When $value is a JSON string, decode it.
                if (Json::isJson($value)) {
                    $value = json_decode($value, true);
                }

                if ($blueprint->hasField($fieldHandle)) {
                    /** @var \Statamic\Fields\Field $field */
                    $field = $blueprint->field($fieldHandle);

                    return $field->setValue($value)->augment()->value();
                }

                return $value;
            })
            ->undot()
            ->merge([
                'url' => $resource->hasRouting() ? $record->uri() : null,
            ])
            ->toArray();
    }

    protected function blueprintFields()
    {
        return $this->resource->blueprint()->fields()->all();
    }

    protected function getFromData($handle)
    {
        return $this->supplements[$handle] ?? $this->data->$handle;
    }
}
