<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Statamic\Facades\Config;
use Statamic\Facades\User;

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
        Post::factory()->count(2)->create();

        $response = $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts']))
            ->assertOk()
            ->assertSee([
                'data',
                'meta',
            ]);

        $ids = collect($response->json()['data'])->pluck('id')->values()->all();

        $this->assertSame($ids, Post::all()->pluck('id')->values()->all());
    }

    /** @test */
    public function returns_not_found_on_a_resource_that_doesnt_exist()
    {
        Post::factory()->count(2)->create();

        $response = $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts2']))
            ->assertNotFound();
    }

    /** @test */
    public function gets_a_resource_model_that_exists()
    {
        Post::factory()->count(2)->create();

        $response = $this
            ->get(route('statamic.api.runway.show', ['handle' => 'posts', 'id' => 1]))
            ->assertOk()
            ->assertSee([
                'data',
            ]);
    }

    /** @test */
    public function returns_not_found_on_a_model_that_doesnt_exist()
    {
        Post::factory()->count(2)->create();
        $user = User::make()->makeSuper()->save();

        $response = $this
            ->get(route('statamic.api.runway.show', ['handle' => 'posts', 'id' => 44]))
            ->assertNotFound();
    }

    /** @test */
    public function paginates_a_resource_list()
    {
        Post::factory()->count(10)->create();

        $response = $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts', 'limit' => 5]))
            ->assertOk()
            ->assertSee([
                'data',
                'meta',
            ]);

        $json = $response->json();

        $this->assertSame($json['meta']['current_page'], 1);
        $this->assertSame($json['meta']['last_page'], 2);
        $this->assertSame($json['meta']['total'], 10);

        $response = $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts', 'limit' => 5, 'page' => 2]))
            ->assertOk()
            ->assertSee([
                'data',
                'meta',
            ]);

        $json = $response->json();

        $this->assertSame($json['meta']['current_page'], 2);
        $this->assertSame($json['meta']['last_page'], 2);
        $this->assertSame($json['meta']['total'], 10);
    }

    /** @test */
    public function filters_a_resource_list()
    {
        Post::factory()->count(3)->create();

        Post::find(1)->fill(['title' => 'Test One'])->save();
        Post::find(2)->fill(['title' => 'Test Two'])->save();
        Post::find(3)->fill(['title' => 'Test Three'])->save();

        $response = $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts']))
            ->assertOk()
            ->assertSee([
                'data',
                'meta',
            ]);

        $json = $response->json();

        $this->assertSame($json['meta']['total'], 3);

        $response = $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts', 'filter[title:contains]' => 'one']))
            ->assertOk()
            ->assertSee([
                'data',
                'meta',
            ]);

        $json = $response->json();

        $this->assertSame($json['meta']['total'], 1);

        $response = $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts', 'filter[title:contains]' => 'test']))
            ->assertOk()
            ->assertSee([
                'data',
                'meta',
            ]);

        $json = $response->json();

        $this->assertSame($json['meta']['total'], 3);
    }

    /** @test */
    public function wont_filter_a_resource_list_on_a_forbidden_filter()
    {
        Post::factory()->count(3)->create();

        Post::find(1)->fill(['title' => 'Test One'])->save();
        Post::find(2)->fill(['title' => 'Test Two'])->save();
        Post::find(3)->fill(['title' => 'Test Three'])->save();

        $this
            ->get(route('statamic.api.runway.index', ['handle' => 'posts', 'filter[slug:contains]' => 'one']))
            ->assertStatus(422);
    }
}
