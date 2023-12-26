<?php

namespace StatamicRadPack\Runway\Tests;

use StatamicRadPack\Runway\Runway;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Fieldset;

class ResourceTest extends TestCase
{
    /** @test */
    public function can_get_eloquent_relationships_for_belongs_to_field()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $eloquentRelationships = $resource->eloquentRelationships();

        $this->assertContains('author', $eloquentRelationships->toArray());
    }

    /** @test */
    public function can_get_eloquent_relationships_for_has_many_field()
    {
        $fields = Config::get('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Author.blueprint.sections.main.fields');

        $fields[] = [
            'handle' => 'posts',
            'field' => [
                'type' => 'has_many',
                'resource' => 'post',
                'max_items' => 1,
                'mode' => 'default',
            ],
        ];

        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Author.blueprint.sections.main.fields', $fields);

        Runway::discoverResources();

        $resource = Runway::findResource('author');

        $eloquentRelationships = $resource->eloquentRelationships();

        $this->assertContains('posts', $eloquentRelationships->toArray());
    }

    /** @test */
    public function can_get_eloquent_relationships_for_runway_uri_routing()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $eloquentRelationships = $resource->eloquentRelationships();

        $this->assertContains('runwayUri', $eloquentRelationships->toArray());
    }

    /** @test */
    public function can_get_eloquent_relationships_as_defined_in_config()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.relationships', ['author']);

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $eloquentRelationships = $resource->eloquentRelationships();

        $this->assertContains('author', $eloquentRelationships->toArray());
        $this->assertNotContains('runwayUri', $eloquentRelationships->toArray());
    }

    /** @test */
    public function can_get_generated_singular()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $singular = $resource->singular();

        $this->assertEquals($singular, 'Post');
    }

    /** @test */
    public function can_get_configured_singular()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.singular', 'Bibliothek');

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $singular = $resource->singular();

        $this->assertEquals($singular, 'Bibliothek');
    }

    /** @test */
    public function can_get_generated_plural()
    {
        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $plural = $resource->plural();

        $this->assertEquals($plural, 'Posts');
    }

    /** @test */
    public function can_get_configured_plural()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.plural', 'Bibliotheken');

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        $plural = $resource->plural();

        $this->assertEquals($plural, 'Bibliotheken');
    }

    /** @test */
    public function can_get_listable_columns()
    {
        Fieldset::make('seo')->setContents([
            'fields' => [
                ['handle' => 'seo_title', 'field' => ['type' => 'text', 'listable' => true]],
                ['handle' => 'seo_description', 'field' => ['type' => 'textarea', 'listable' => true]],
            ],
        ])->save();

        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.blueprint', [
            'sections' => [
                'main' => [
                    'fields' => [
                        ['handle' => 'title', 'field' => ['type' => 'text', 'listable' => true]],
                        ['handle' => 'summary', 'field' => ['type' => 'textarea', 'listable' => true]],
                        ['handle' => 'body', 'field' => ['type' => 'markdown', 'listable' => 'hidden']],
                        ['handle' => 'thumbnail', 'field' => ['type' => 'assets', 'listable' => false]],
                        ['import' => 'seo'],
                    ],
                ],
            ],
        ]);

        $resource = Runway::discoverResources()->findResource('post');

        $this->assertEquals([
            'title',
            'summary',
            'seo_title',
            'seo_description',
        ], $resource->listableColumns()->toArray());
    }
}
