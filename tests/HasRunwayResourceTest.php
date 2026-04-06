<?php

namespace StatamicRadPack\Runway\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\ExternalPost;

class HasRunwayResourceTest extends TestCase
{
    #[Test]
    public function scope_runway_search_works_with_custom_eloquent_connection()
    {
        Config::set('database.connections.external', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Schema::connection('external')->create('external_posts', function ($table) {
            $table->id();
            $table->string('title');
            $table->longText('body');
            $table->timestamps();
        });

        Runway::registerResource(ExternalPost::class, []);

        ExternalPost::create([
            'title' => 'Test External Post',
            'body' => 'This is the body of the test post.',
        ]);

        ExternalPost::create([
            'title' => 'Another Post',
            'body' => 'This is different content.',
        ]);

        ExternalPost::create([
            'title' => 'Something Else',
            'body' => 'No matching content here.',
        ]);

        $results = ExternalPost::query()->runwaySearch('Test External')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Test External Post', $results->first()->title);
    }
}
