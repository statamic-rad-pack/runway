<?php

namespace DoubleThreeDigital\Runway\Routing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RunwayUri extends Model
{
    protected $fillable = ['uri', 'model_type', 'model_id'];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
