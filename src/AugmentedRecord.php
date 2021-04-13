<?php

namespace DoubleThreeDigital\Runway;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Statamic\Fields\Blueprint;

class AugmentedRecord
{
    public static function augment(Model $record, Blueprint $blueprint)
    {
        return collect($record)
            ->map(function ($value, $key) use ($blueprint) {
                if ($value instanceof CarbonInterface) {
                    return $value->format('Y-m-d H:i');
                }

                if ($blueprint->hasField($key)) {
                    return $blueprint->field($key)->fieldtype()->augment($value);
                }

                return $value;
            })
            ->toArray();
    }
}
