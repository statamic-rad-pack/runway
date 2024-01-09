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

    /** @test */
    public function can_get_generated_singular()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $singular = $resource->singular();

        $this->assertSame($singular, 'Post');
    }

    /** @test */
    public function can_get_configured_singular()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Post.singular', 'Bibliothek');

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $singular = $resource->singular();

        $this->assertSame($singular, 'Bibliothek');
    }

    /** @test */
    public function can_get_generated_plural()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $plural = $resource->plural();

        $this->assertSame($plural, 'Posts');
    }

    /** @test */
    public function can_get_configured_plural()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Post.plural', 'Bibliotheken');

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $plural = $resource->plural();

        $this->assertSame($plural, 'Bibliotheken');
    }

    /** @test */
    public function can_get_listable_columns()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Post.blueprint.sections.main.fields', [
            [
                'handle' => 'values->normal_field',
                'field' => ['type' => 'text'],
            ],
            [
                'handle' => 'values->listable_hidden_field',
                'field' => ['type' => 'text', 'listable' => 'hidden'],
            ],
            [
                'handle' => 'values->listable_shown_field',
                'field' => ['type' => 'text', 'listable' => true],
            ],
            [
                'handle' => 'values->not_listable_field',
                'field' => ['type' => 'text', 'listable' => false],
            ],
        ]);

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $listableColumns = $resource->listableColumns();

        $this->assertCount(3, $listableColumns);
        $this->assertEquals([
            'values->normal_field',
            'values->listable_hidden_field',
            'values->listable_shown_field',
        ], $listableColumns);
    }

    /** @test */
    public function can_get_title_field()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Post.blueprint.sections.main.fields', [
            [
                'handle' => 'values->listable_hidden_field',
                'field' => ['type' => 'text', 'listable' => 'hidden'],
            ],
            [
                'handle' => 'values->listable_shown_field',
                'field' => ['type' => 'text', 'listable' => true],
            ],
            [
                'handle' => 'values->not_listable_field',
                'field' => ['type' => 'text', 'listable' => false],
            ],
        ]);

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $this->assertEquals('values->listable_shown_field', $resource->titleField());
    }
}
