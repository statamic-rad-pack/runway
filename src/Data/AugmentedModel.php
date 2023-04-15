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

    public function __construct($model)
    {
        $this->data = $model;
        $this->resource = Runway::findResourceByModel($model);
    }

    public function keys()
    {
        return collect()
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
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
            ->mapWithKeys(fn ($fieldHandle) => [$fieldHandle => $record->{$fieldHandle}]);

        return collect($modelKeyValue)
            ->merge($resourceKeyValue)
            ->map(function ($value, $key) use ($record, $resource, $blueprint) {
                $value = $record->{$key} ?? $value;

                // When $value is a Carbon instance, format it with the format
                // specified in the blueprint.
                if ($value instanceof CarbonInterface) {
                    $format = $defaultFormat = 'Y-m-d H:i';

                    if ($field = $resource->blueprint()->field($key)) {
                        $format = $field->get('format', $defaultFormat);
                    }

                    return $value->format($format);
                }

                // When $value is a JSON string, decode it.
                if (Json::isJson($value)) {
                    $value = json_decode($value, true);
                }

                if ($blueprint->hasField($key)) {
                    /** @var \Statamic\Fields\Field $field */
                    $field = $blueprint->field($key);

                    return $field->setValue($value)->augment()->value();
                }

                return $value;
            })
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
        return $this->data->$handle;
    }
}
