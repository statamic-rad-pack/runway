<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'body', 'images', 'publish_at',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
    ];
}
