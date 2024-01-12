<?php

namespace StatamicRadPack\Runway\Tests\Routing;

use Statamic\Facades\Data;
use StatamicRadPack\Runway\Routing\RoutingModel;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ResourceRoutingRepositoryTest extends TestCase
{
    /** @test */
    public function can_find_by_uri()
    {
        $post = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertEquals($runwayUri->uri, "/posts/{$post->slug}");

        $findByUri = Data::findByUri("/posts/{$post->slug}");

        $this->assertEquals($post->fresh()->id, $findByUri->id);
        $this->assertTrue($findByUri instanceof RoutingModel);
    }

    /** @test */
    public function can_find_by_uri_where_multiple_matches_are_found()
    {
        $posts = Post::factory()->count(5)->create(['slug' => 'chicken-fried-rice']);

        $findByUri = Data::findByUri("/posts/{$posts[0]->slug}");

        $this->assertEquals($posts[0]->id, $findByUri->id);
        $this->assertTrue($findByUri instanceof RoutingModel);
    }

    /** @test */
    public function cant_find_by_uri_if_no_matching_uri()
    {
        $findByUri = Data::findByUri('/posts/some-absolute-jibber-jabber');

        $this->assertNull($findByUri);
    }

    /** @test */
    public function cant_find_by_uri_if_a_similar_uri_exists()
    {
        $post = Post::factory()->create();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertEquals($runwayUri->uri, "/posts/{$post->slug}");

        $findByUri = Data::findByUri("/posts/{$post->slug}-smth");

        $this->assertNull($findByUri);
    }
}
