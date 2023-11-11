<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Statamic\Facades\Config;

class ApiControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('statamic.api.resources.runway', [
            'posts' => ['allowed_filters' => ['title']],
        ]);
    }

    /** @test */
    public function gets_a_resource_that_exists()
    {
        $posts = Post::factory()->count(2)->create();

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts']))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('data.0.id', $posts[0]->id)
            ->assertJsonPath('data.1.id', $posts[1]->id);
    }

    /** @test */
    public function returns_not_found_on_a_resource_that_doesnt_exist()
    {
        Post::factory()->count(2)->create();

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts2']))
            ->assertNotFound();
    }

    /** @test */
    public function gets_a_resource_model_that_exists()
    {
        $post = Post::factory()->create();

        $this
            ->get(route('statamic.api.runway.show', ['resourceHandle' => 'posts', 'record' => $post->id]))
            ->assertOk()
            ->assertSee(['data'])
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title);
    }

    /** @test */
    public function gets_a_resource_model_with_nested_fields()
    {
        $post = Post::factory()->create([
            'values' => [
                'alt_title' => 'Alternative Title...',
                'alt_body' => 'This is a **great** post! You should *read* it.',
            ],
        ]);

        $this
            ->get(route('statamic.api.runway.show', ['resourceHandle' => 'posts', 'record' => $post->id]))
            ->assertOk()
            ->assertSee(['data'])
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.values.alt_title', 'Alternative Title...')
            ->assertJsonPath('data.values.alt_body', '<p>This is a <strong>great</strong> post! You should <em>read</em> it.</p>
');
    }

    /** @test */
    public function returns_not_found_on_a_model_that_does_not_exist()
    {
        $this
            ->get(route('statamic.api.runway.show', ['resourceHandle' => 'posts', 'record' => 44]))
            ->assertNotFound();
    }

    /** @test */
    public function paginates_a_resource_list()
    {
        Post::factory()->count(10)->create();

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts', 'limit' => 5]))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 10);

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts', 'limit' => 5, 'page' => 2]))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 10);
    }

    /** @test */
    public function filters_a_resource_list()
    {
        [$postA, $postB, $postC] = Post::factory()->count(3)->create();

        $postA->update(['title' => 'Test One']);
        $postB->update(['title' => 'Test Two']);
        $postC->update(['title' => 'Test Three']);

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts']))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('meta.total', 3);

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts', 'filter[title:contains]' => 'one']))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('meta.total', 1);

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts', 'filter[title:contains]' => 'test']))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('meta.total', 3);
    }

    /** @test */
    public function wont_filter_a_resource_list_on_a_forbidden_filter()
    {
        [$postA, $postB, $postC] = Post::factory()->count(3)->create();

        $postA->update(['title' => 'Test One']);
        $postB->update(['title' => 'Test Two']);
        $postC->update(['title' => 'Test Three']);

        $this
            ->get(route('statamic.api.runway.index', ['resourceHandle' => 'posts', 'filter[slug:contains]' => 'one']))
            ->assertStatus(422);
    }
}
