<?php

namespace RunwayRelationshipsSandbox;

use Illuminate\Database\Eloquent\Model;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class Author extends Model
{
    use HasRunwayResource;

    protected $table = 'authors';
    protected $guarded = [];
}

class Notebook extends Model
{
    use HasRunwayResource;

    protected $table = 'notebooks';
    protected $guarded = [];
    protected $casts = ['selections' => 'array'];
}
