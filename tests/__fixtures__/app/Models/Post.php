<?php

namespace StatamicRadPack\Runway\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use StatamicRadPack\Runway\Routing\Traits\RunwayRoutes;
use StatamicRadPack\Runway\Tests\Fixtures\Database\Factories\PostFactory;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class Post extends Model
{
    use HasFactory, HasRunwayResource, RunwayRoutes;

    protected $fillable = [
        'title', 'slug', 'body', 'values', 'external_links', 'author_id', 'sort_order',
    ];

    protected $appends = [
        'excerpt',
    ];

    protected $casts = [
        'values' => 'array',
        'external_links' => 'object',
    ];

    public function scopeFood($query)
    {
        $query->whereIn('title', ['Pasta', 'Apple', 'Burger']);
    }

    public function scopeFruit($query, $smth)
    {
        if ($smth === 'idoo') {
            $query->whereIn('title', ['Apple']);
        }
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function getExcerptAttribute()
    {
        return 'This is an excerpt.';
    }

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
