<?php

namespace StatamicRadPack\Runway\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class ExternalPost extends Model
{
    use HasRunwayResource;

    protected $connection = 'external';

    protected $table = 'external_posts';

    protected $fillable = [
        'title',
        'body',
    ];
}
