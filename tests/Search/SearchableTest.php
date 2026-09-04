<?php

namespace StatamicRadPack\Runway\Tests\Search;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Runway\Data\AugmentedModel;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Search\Searchable;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class SearchableTest extends TestCase
{
    #[Test]
    public function can_get_resource()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertEquals(Runway::findResource('post'), $searchable->resource());
    }

    #[Test]
    public function can_get_queryable_value()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertEquals($post->title, $searchable->getQueryableValue('title'));
        $this->assertEquals($post->slug, $searchable->getQueryableValue('slug'));
        $this->assertEquals($post->id, $searchable->getQueryableValue('id'));
        $this->assertEquals('default', $searchable->getQueryableValue('site'));
    }

    #[Test]
    public function can_get_queryable_value_from_nested_field()
    {
        $post = Post::factory()->create(['values' => ['alt_title' => 'Alternative Title']]);

        $searchable = new Searchable($post);

        $this->assertEquals('Alternative Title', $searchable->getQueryableValue('values_alt_title'));
    }

    #[Test]
    public function can_get_search_value()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertEquals($post->title, $searchable->getSearchValue('title'));
        $this->assertEquals($post->slug, $searchable->getSearchValue('slug'));
        $this->assertEquals($post->id, $searchable->getSearchValue('id'));
        $this->assertEquals($post->searchMethod(), $searchable->getSearchValue('searchMethod'));
    }

    #[Test]
    public function can_get_search_value_from_nested_field()
    {
        $post = Post::factory()->create(['values' => ['alt_title' => 'Alternative Title']]);

        $searchable = new Searchable($post);

        $this->assertEquals('Alternative Title', $searchable->getSearchValue('values_alt_title'));
    }

    #[Test]
    public function can_get_search_reference()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertEquals("runway::post::{$post->id}", $searchable->getSearchReference());
    }

    #[Test]
    public function can_get_search_result()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $result = $searchable->toSearchResult();

        $this->assertEquals($searchable, $result->getSearchable());
        $this->assertEquals("runway::post::{$post->id}", $result->getReference());
        $this->assertEquals('runway:post', $result->getType());
    }

    #[Test]
    public function can_get_cp_search_result_title()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertEquals($post->title, $searchable->getCpSearchResultTitle());
    }

    #[Test]
    public function can_get_cp_search_result_title_when_title_field_is_a_nested_field()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.title_field', 'values_alt_title');

        Runway::discoverResources();

        $post = Post::factory()->create(['values' => ['alt_title' => 'Alternative Title']]);

        $searchable = new Searchable($post);

        $this->assertEquals('Alternative Title', $searchable->getCpSearchResultTitle());
    }

    #[Test]
    public function can_get_cp_search_result_url()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertStringContainsString("/runway/post/{$post->id}", $searchable->getCpSearchResultUrl());
    }

    #[Test]
    public function can_get_cp_search_result_badge()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertEquals('Posts', $searchable->getCpSearchResultBadge());
    }

    #[Test]
    public function can_get_cp_search_result_icon()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertIsString($searchable->getCpSearchResultIcon());
    }

    #[Test]
    public function can_get_new_augmented_instance()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);
        $searchable->setSupplement('foo', 'bar');

        $this->assertInstanceOf(AugmentedModel::class, $searchable->newAugmentedInstance());
    }
}
