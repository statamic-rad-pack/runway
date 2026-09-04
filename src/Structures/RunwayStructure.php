<?php

namespace StatamicRadPack\Runway\Structures;

use Illuminate\Database\Eloquent\Model;

class RunwayStructure extends Model
{
    protected $fillable = ['model_type', 'model_id', 'order'];

    public function getTable()
    {
        return config('runway.tables.structures', 'runway_structures');
    }

    public static function nextOrderFor(Model $model): int
    {
        return static::query()->where('model_type', $model->getMorphClass())->max('order') + 1;
    }
}
