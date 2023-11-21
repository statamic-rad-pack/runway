<?php

namespace DoubleThreeDigital\Runway\Tests\Console\Commands;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ListResourcesTest extends TestCase
{
    /** @test */
    public function it_lists_resources()
    {
        $this
            ->artisan('runway:resources')
            ->expectsTable(
                ['Handle', 'Model', 'Route'],
                [
                    ['post', Post::class, '/posts/{{ slug }}'],
                    ['author', Author::class, 'N/A'],
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
