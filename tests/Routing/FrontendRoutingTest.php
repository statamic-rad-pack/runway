<?php

namespace DoubleThreeDigital\Runway\Tests\Routing;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class FrontendRoutingTest extends TestCase
{
    /** @test */
    public function returns_resource_response_for_resource()
    {
        $post = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this
            ->get($runwayUri->uri)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('TEMPLATE: default')
            ->assertSee('LAYOUT: layout');
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/pull/302
     */
    public function returns_resource_response_for_resource_with_nested_field()
    {
        $post = Post::factory()->create([
            'values' => [
                'alt_title' => 'Alternative Title...',
            ],
        ]);

        $runwayUri = $post->fresh()->runwayUri;

        $this
            ->get($runwayUri->uri)
            ->assertOk()
            ->assertSee('Alternative Title...')
            ->assertSee('TEMPLATE: default')
            ->assertSee('LAYOUT: layout');
    }

    /** @test */
    public function returns_resource_response_for_resource_with_custom_template()
    {
        Config::set('runway.resources.'.Post::class.'.template', 'custom');

        Runway::discoverResources();

        $post = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this
            ->get($runwayUri->uri)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('TEMPLATE: custom')
            ->assertSee('LAYOUT: layout');
    }

    /** @test */
    public function returns_resource_response_for_resource_with_custom_layout()
    {
        Config::set('runway.resources.'.Post::class.'.layout', 'blog-layout');

        Runway::discoverResources();

        $post = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this
            ->get($runwayUri->uri)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('TEMPLATE: default')
            ->assertSee('LAYOUT: blog-layout');
    }
}
