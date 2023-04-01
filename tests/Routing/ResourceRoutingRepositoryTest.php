<?php

namespace DuncanMcClean\Runway\Tests\Routing;

use DuncanMcClean\Runway\Routing\RoutingModel;
use DuncanMcClean\Runway\Tests\TestCase;
use Statamic\Facades\Data;

class ResourceRoutingRepositoryTest extends TestCase
{
    /** @test */
    public function can_find_by_uri()
    {
        $post = $this->postFactory();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertSame($runwayUri->uri, "/posts/{$post->slug}");

        $findByUri = Data::findByUri("/posts/{$post->slug}");

        $this->assertSame($post->fresh()->id, $findByUri->id);
        $this->assertTrue($findByUri instanceof RoutingModel);
    }

    /** @test */
    public function can_find_by_uri_where_multiple_matches_are_found()
    {
        $posts = $this->postFactory(5, [
            'slug' => 'chicken-fried-rice',
        ]);

        $findByUri = Data::findByUri("/posts/{$posts[0]->slug}");

        $this->assertSame($posts[0]->id, $findByUri->id);
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
        $post = $this->postFactory();
        $runwayUri = $post->fresh()->runwayUri;

        $this->assertSame($runwayUri->uri, "/posts/{$post->slug}");

        $findByUri = Data::findByUri("/posts/{$post->slug}-smth");

        $this->assertNull($findByUri);
    }
}
