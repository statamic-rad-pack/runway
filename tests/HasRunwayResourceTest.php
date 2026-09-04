<?php

namespace StatamicRadPack\Runway\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Structures\RunwayStructure;
use StatamicRadPack\Runway\Tests\Fixtures\Models\ExternalPost;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;

class HasRunwayResourceTest extends TestCase
{
    #[Test]
    public function it_gets_a_field_column()
    {
        $post = Post::factory()->create();

        $this->assertEquals('title', $post->getFieldColumn('title'));
        $this->assertEquals('values->alt_title', $post->getFieldColumn('values_alt_title'));
        $this->assertEquals('external_links->links', $post->getFieldColumn('external_links_links'));
        $this->assertEquals('values', $post->getFieldColumn('values'));
    }

    #[Test]
    public function it_gets_a_field_value()
    {
        $post = Post::factory()->create([
            'title' => 'Hello World',
            'values' => ['alt_title' => 'Alternative Title...'],
            'external_links' => ['links' => [['label' => 'Statamic', 'url' => 'https://statamic.com']]],
        ]);

        $this->assertEquals('Hello World', $post->getFieldValue('title'));
        $this->assertEquals('Alternative Title...', $post->getFieldValue('values_alt_title'));
        $this->assertEquals('Statamic', $post->getFieldValue('external_links_links')[0]->label);
        $this->assertNull($post->getFieldValue('values_missing_key'));

        // The resource might not have a title field at all.
        $this->assertNull($post->getFieldValue(null));
    }

    #[Test]
    public function scope_runway_search_searches_nested_fields()
    {
        Post::factory()->create(['title' => 'Hello World', 'values' => ['alt_title' => 'Alternative Title...']]);
        Post::factory()->create(['title' => 'Goodbye World', 'values' => ['alt_title' => 'Something Else...']]);

        $results = Post::query()->runwaySearch('Alternative')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Hello World', $results->first()->title);
    }

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

    #[Test]
    public function structure_is_created_when_model_of_orderable_resource_is_created()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.orderable', true);

        Runway::discoverResources();

        $posts = Post::factory()->count(3)->create();

        $this->assertEquals(1, $posts[0]->runwayStructure->order);
        $this->assertEquals(2, $posts[1]->runwayStructure->order);
        $this->assertEquals(3, $posts[2]->runwayStructure->order);
    }

    #[Test]
    public function structure_isnt_created_when_resource_isnt_orderable()
    {
        Post::factory()->count(2)->create();

        $this->assertDatabaseEmpty(RunwayStructure::class);
    }

    #[Test]
    public function structure_is_deleted_when_model_of_orderable_resource_is_deleted()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.orderable', true);

        Runway::discoverResources();

        $post = Post::factory()->create();

        $post->delete();

        $this->assertDatabaseEmpty(RunwayStructure::class);
    }
}
