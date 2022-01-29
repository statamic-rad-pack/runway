<?php

namespace DoubleThreeDigital\Runway\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunwayUri extends Model
{
    use HasFactory;

    protected $fillable = [
        'uri', 'model_type', 'model_id',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
