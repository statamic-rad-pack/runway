<?php

namespace DoubleThreeDigital\Runway;

use Carbon\CarbonInterface;
use DoubleThreeDigital\Runway\Support\Json;
use Illuminate\Database\Eloquent\Model;
use Statamic\Fields\Blueprint;

class AugmentedRecord
{
    public static function augment(Model $record, Blueprint $blueprint): array
    {
        $resource = Runway::findResourceByModel($record);

        return collect($record)
            ->map(function ($value, $key) use ($record, $resource, $blueprint) {

                $value = $record->{$key} ?? $value;

                if ($value instanceof CarbonInterface) {
                    $format = $defaultFormat = 'Y-m-d H:i';

                    if ($field = $resource->blueprint()->field($key)) {
                        $format = $field->get('format', $defaultFormat);
                    }

                    return $value->format($format);
                }

                if (Json::isJson($value)) {
                    $value = json_decode($value, true);
                }

                if ($blueprint->hasField($key)) {
                    return $blueprint->field($key)->setValue($value)->augment()->value();
                }

                return $value;
            })
            ->merge([
                'url' => $resource->hasRouting() ? $record->uri() : null,
            ])
            ->toArray();
    }
}
