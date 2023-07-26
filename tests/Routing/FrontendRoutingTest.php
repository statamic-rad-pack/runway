<?php

namespace DoubleThreeDigital\Runway\Tests\Routing;

use DoubleThreeDigital\Runway\Tests\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class FrontendRoutingTest extends TestCase
{
    /** @test */
    public function returns_resource_response_for_resource()
    {
        $post = $this->postFactory();
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
        $post = $this->postFactory(
            attributes: [
                'values' => [
                    'alt_title' => $this->faker->words(6, asText: true),
                ],
            ],
        );

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

        $post = $this->postFactory();
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

        $post = $this->postFactory();
        $runwayUri = $post->fresh()->runwayUri;

        $this
            ->get($runwayUri->uri)
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('TEMPLATE: default')
            ->assertSee('LAYOUT: blog-layout');
    }
}
