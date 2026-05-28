<?php

namespace StatamicRadPack\Runway\Tests;

use Facades\Statamic\CP\LivePreview;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Data;
use StatamicRadPack\Runway\Routing\RoutingModel;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;

class ModelRepositoryTest extends TestCase
{
    #[Test]
    public function can_find_by_reference()
    {
        $post = Post::factory()->create();

        $found = Data::find("runway::post::{$post->id}");

        $this->assertNotNull($found);
        $this->assertEquals($post->id, $found->id);
    }

    #[Test]
    public function cant_find_by_reference_when_model_doesnt_exist()
    {
        $found = Data::find('runway::post::99999');

        $this->assertNull($found);
    }

    #[Test]
    public function cant_find_by_reference_when_resource_doesnt_exist()
    {
        $found = Data::find('runway::nonexistent::12345');

        $this->assertNull($found);
    }

    #[Test]
    public function can_find_by_uri()
    {
        $post = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertEquals($runwayUri->uri, "/posts/{$post->slug}");

        $findByUri = Data::findByUri("/posts/{$post->slug}");

        $this->assertEquals($post->fresh()->id, $findByUri->id);
        $this->assertTrue($findByUri instanceof RoutingModel);
    }

    #[Test]
    public function can_find_by_uri_where_multiple_matches_are_found()
    {
        $posts = Post::factory()->count(5)->create(['slug' => 'chicken-fried-rice']);

        $findByUri = Data::findByUri("/posts/{$posts[0]->slug}");

        $this->assertEquals($posts[0]->id, $findByUri->id);
        $this->assertTrue($findByUri instanceof RoutingModel);
    }

    #[Test]
    public function cant_find_by_uri_if_no_matching_uri()
    {
        $findByUri = Data::findByUri('/posts/some-absolute-jibber-jabber');

        $this->assertNull($findByUri);
    }

    #[Test]
    public function cant_find_by_uri_if_a_similar_uri_exists()
    {
        $post = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertEquals($runwayUri->uri, "/posts/{$post->slug}");

        $findByUri = Data::findByUri("/posts/{$post->slug}-smth");

        $this->assertNull($findByUri);
    }

    #[Test]
    public function cant_find_by_uri_when_model_is_unpublished()
    {
        $post = Post::factory()->unpublished()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertEquals($runwayUri->uri, "/posts/{$post->slug}");

        $findByUri = Data::findByUri("/posts/{$post->slug}");

        $this->assertNull($findByUri);
    }

    #[Test]
    public function can_find_by_uri_when_model_is_unpublished_with_live_preview_token()
    {
        $post = Post::factory()->unpublished()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertEquals($runwayUri->uri, "/posts/{$post->slug}");

        LivePreview::tokenize('test-token', $post);

        $findByUri = $this
            ->withServerVariables(['QUERY_STRING' => 'token=test-token'])
            ->call('GET', "/posts/{$post->slug}", ['token' => 'test-token'])
            ->baseResponse;

        $this->get("/posts/{$post->slug}?token=test-token");

        $findByUri = Data::findByUri("/posts/{$post->slug}");

        $this->assertNotNull($findByUri);
        $this->assertEquals($post->fresh()->id, $findByUri->id);
    }

    #[Test]
    public function cant_find_by_uri_when_model_is_unpublished_with_live_preview_token_for_different_model()
    {
        $post = Post::factory()->unpublished()->create();
        $otherPost = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertEquals($runwayUri->uri, "/posts/{$post->slug}");

        LivePreview::tokenize('test-token', $otherPost);

        $this->get("/posts/{$post->slug}?token=test-token");

        $findByUri = Data::findByUri("/posts/{$post->slug}");

        $this->assertNull($findByUri);
    }
}
