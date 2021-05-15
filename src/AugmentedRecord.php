<?php

namespace DoubleThreeDigital\Runway;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Statamic\Fields\Blueprint;

class AugmentedRecord
{
    public static function augment(Model $record, Blueprint $blueprint): array
    {
        $resource = Runway::findResourceByModel($record);

        return collect($record)
            ->map(function ($value, $key) use ($blueprint) {
                if ($value instanceof CarbonInterface) {
                    return $value->format('Y-m-d H:i');
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
