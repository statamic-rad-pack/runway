<?php

namespace DoubleThreeDigital\Runway\Tests\Search;

use DoubleThreeDigital\Runway\Data\AugmentedModel;
use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Search\Searchable;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;

class SearchableTest extends TestCase
{
    /** @test */
    public function can_get_resource()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertSame(Runway::findResource('post'), $searchable->resource());
    }

    /** @test */
    public function can_get_queryable_value()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertSame($post->title, $searchable->getQueryableValue('title'));
        $this->assertSame($post->slug, $searchable->getQueryableValue('slug'));
        $this->assertSame($post->id, $searchable->getQueryableValue('id'));
        $this->assertSame('default', $searchable->getQueryableValue('site'));
    }

    /** @test */
    public function can_get_search_value()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertSame($post->title, $searchable->getSearchValue('title'));
        $this->assertSame($post->slug, $searchable->getSearchValue('slug'));
        $this->assertSame($post->id, $searchable->getSearchValue('id'));
    }

    /** @test */
    public function can_get_search_reference()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertSame("runway::post::{$post->id}", $searchable->getSearchReference());
    }

    /** @test */
    public function can_get_search_result()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $result = $searchable->toSearchResult();

        $this->assertSame($searchable, $result->getSearchable());
        $this->assertSame("runway::post::{$post->id}", $result->getReference());
        $this->assertSame('runway:post', $result->getType());
    }

    /** @test */
    public function can_get_cp_search_result_title()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertSame($post->title, $searchable->getCpSearchResultTitle());
    }

    /** @test */
    public function can_get_cp_search_result_url()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertStringContainsString("/runway/post/{$post->id}", $searchable->getCpSearchResultUrl());
    }

    /** @test */
    public function can_get_cp_search_result_badge()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);

        $this->assertSame('Posts', $searchable->getCpSearchResultBadge());
    }

    /** @test */
    public function can_get_new_augmented_instance()
    {
        $post = Post::factory()->create();

        $searchable = new Searchable($post);
        $searchable->setSupplement('foo', 'bar');

        $this->assertInstanceOf(AugmentedModel::class, $searchable->newAugmentedInstance());
    }
}
