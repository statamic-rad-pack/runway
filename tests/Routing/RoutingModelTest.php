<?php

namespace StatamicRadPack\Runway\Tests\Routing;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use StatamicRadPack\Runway\Routing\RoutingModel;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class RoutingModelTest extends TestCase
{
    /** @test */
    public function can_get_route()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals('/blog/post-slug', $routingModel->route());
    }

    /** @test */
    public function cant_get_route_without_runway_uri()
    {
        $post = Post::factory()->createQuietly();

        $routingModel = new RoutingModel($post);

        $this->assertNull($routingModel->route());
    }

    /** @test */
    public function can_get_route_data()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals([
            'id' => $post->id,
        ], $routingModel->routeData());
    }

    /** @test */
    public function can_get_uri()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals('/blog/post-slug', $routingModel->uri());
    }

    /** @test */
    public function can_get_url_without_redirect()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals('/blog/post-slug', $routingModel->urlWithoutRedirect());
    }

    /** @test */
    public function can_get_response()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $response = $routingModel->toResponse(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString("<h1>{$post->title}</h1>", $response->getContent());
        $this->assertStringContainsString("<article>{$post->body}</article>", $response->getContent());
    }

    /** @test */
    public function can_get_template()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.template', 'posts.show');
        Runway::discoverResources();

        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals('posts.show', $routingModel->template());
    }

    /** @test */
    public function can_get_layout()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.layout', 'layouts.post');
        Runway::discoverResources();

        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals('layouts.post', $routingModel->layout());
    }

    /** @test */
    public function can_get_id()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals($post->id, $routingModel->id());
    }

    /** @test */
    public function can_get_route_key()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals($post->id, $routingModel->getRouteKey());
    }

    /** @test */
    public function can_get_augmented_array_data()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals($post->toArray(), $routingModel->augmentedArrayData());
    }

    /** @test */
    public function can_get_something_from_model()
    {
        $post = Post::factory()->createQuietly();
        $post->runwayUri()->create(['uri' => '/blog/post-slug']);

        $routingModel = new RoutingModel($post);

        $this->assertEquals($post->slug, $routingModel->slug);
    }
}
