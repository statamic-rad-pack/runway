<?php

namespace StatamicRadPack\Runway\Tests\Routing;

use Illuminate\Support\Facades\Config;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

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
     * https://github.com/statamic-rad-pack/runway/pull/302
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
