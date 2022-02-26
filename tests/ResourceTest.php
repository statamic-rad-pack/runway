<?php

namespace DoubleThreeDigital\Runway\Tests;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Facades\Config;

class ResourceTest extends TestCase
{
    /** @test */
    public function can_get_eager_loading_relations_for_belongs_to_field()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $eagerLoadingRelations = $resource->eagerLoadingRelations();

        $this->assertContains('author', $eagerLoadingRelations->toArray());
    }

    /** @test */
    public function can_get_eager_loading_relations_for_has_many_field()
    {
        $fields = Config::get('runway.resources.DoubleThreeDigital\Runway\Tests\Author.blueprint.sections.main.fields');

        $fields[] = [
            'handle' => 'posts',
            'field' => [
                'type' => 'has_many',
                'resource' => 'post',
                'max_items' => 1,
                'mode' => 'default',
            ],
        ];

        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Author.blueprint.sections.main.fields', $fields);

        Runway::discoverResources();

        $resource = Runway::findResource('author');

        $eagerLoadingRelations = $resource->eagerLoadingRelations();

        $this->assertContains('posts', $eagerLoadingRelations->toArray());
    }

    /** @test */
    public function can_get_eager_loading_relations_for_runway_uri_routing()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $eagerLoadingRelations = $resource->eagerLoadingRelations();

        $this->assertContains('runwayUri', $eagerLoadingRelations->toArray());
    }

    /** @test */
    public function can_get_eager_loading_relations_as_defined_in_config()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Post.with', ['author']);

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $eagerLoadingRelations = $resource->eagerLoadingRelations();

        $this->assertContains('author', $eagerLoadingRelations->toArray());
        $this->assertNotContains('runwayUri', $eagerLoadingRelations->toArray());
    }
}
