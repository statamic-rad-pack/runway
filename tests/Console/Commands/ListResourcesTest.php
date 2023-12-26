<?php

namespace StatamicRadPack\Runway\Tests\Console\Commands;

use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ListResourcesTest extends TestCase
{
    /** @test */
    public function it_lists_resources()
    {
        $this
            ->artisan('runway:resources')
            ->expectsTable(
                ['Handle', 'Model', 'Blueprint', 'Route'],
                [
                    ['post', Post::class, 'post', '/posts/{{ slug }}'],
                    ['author', Author::class, 'author', 'N/A'],
                ]
            );
    }

    /** @test */
    public function it_outputs_error_when_no_resources_exist()
    {
        Config::set('runway.resources', []);
        Runway::discoverResources();

        $this
            ->artisan('runway:resources')
            ->expectsOutput("Your application doesn't have any resources.");
    }
}
