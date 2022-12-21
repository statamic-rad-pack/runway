<?php

namespace DoubleThreeDigital\Runway;

use Carbon\CarbonInterface;
use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Support\Json;
use Illuminate\Database\Eloquent\Model;
use Statamic\Fields\Blueprint;

class AugmentedRecord
{
    /**
     * Takes in an Eloquent model & augments it with the fields
     * from the resource's blueprint.
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
}
