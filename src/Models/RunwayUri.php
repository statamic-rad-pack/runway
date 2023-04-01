<?php

namespace DoubleThreeDigital\Runway\Models;

use Illuminate\Database\Eloquent\Model;

class RunwayUri extends Model
{
    protected $fillable = [
        'uri', 'model_type', 'model_id',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
