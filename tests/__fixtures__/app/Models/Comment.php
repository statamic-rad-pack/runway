<?php

namespace StatamicRadPack\Runway\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class Comment extends Model
{
    use HasFactory, HasRunwayResource;

    protected $fillable = [
        'comment',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
