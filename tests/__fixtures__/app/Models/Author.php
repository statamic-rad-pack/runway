<?php

namespace StatamicRadPack\Runway\Tests\Fixtures\Models;

use StatamicRadPack\Runway\Tests\Fixtures\Database\Factories\AuthorFactory;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory, HasRunwayResource;

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

    protected static function newFactory()
    {
        return AuthorFactory::new();
    }
}
