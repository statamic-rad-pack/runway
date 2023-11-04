<?php

namespace DoubleThreeDigital\Runway\Tests\Fixtures\Models;

use DoubleThreeDigital\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasRunwayResource;

    protected $fillable = [
        'name', 'start_date', 'end_date',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function pivottedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_author');
    }
}
