<?php

namespace DoubleThreeDigital\Runway\Tests\Routing;

use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class FrontendRoutingTest extends TestCase
{
    use WithFaker;

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
                'alt_title' => $this->faker->words(6, asText: true),
            ],
        ]);

        $runwayUri = $post->fresh()->runwayUri;

        $this
            ->get($runwayUri->uri)
            ->assertOk()
            ->assertSee($post->values['alt_title'])
            ->assertSee('TEMPLATE: default')
            ->assertSee('LAYOUT: layout');
    }

    /** @test */
    public function returns_resource_response_for_resource_with_custom_template()
    {
        $this->markTestIncomplete();

        // TODO: find way of mocking the template & rebooting Runway's resources
        Config::set('runway.resources.'.Post::class.'.template', 'custom');

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
        $this->markTestIncomplete();

        // TODO: find way of mocking the template & rebooting Runway's resources
        Config::set('runway.resources.'.Post::class.'.layout', 'blog-layout');

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
