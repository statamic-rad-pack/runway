<?php

namespace StatamicRadPack\Runway\Tests\Console\Commands;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ListResourcesTest extends TestCase
{
    #[Test]
    public function it_lists_resources()
    {
        $this
            ->artisan('runway:resources')
            ->expectsTable(
                ['Handle', 'Model', 'Route'],
                [
                    ['author', Author::class, 'N/A'],
                    ['post', Post::class, '/posts/{{ slug }}'],
                ]
            );
    }

    #[Test]
    public function it_outputs_error_when_no_resources_exist()
    {
        Config::set('runway.resources', []);
        Runway::discoverResources();

        $this
            ->artisan('runway:resources')
            ->expectsOutputToContain("Your application doesn't have any resources.");
    }
}
