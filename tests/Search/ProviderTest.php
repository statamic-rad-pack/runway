<?php

namespace StatamicRadPack\Runway\Tests\Search;

use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Runway\Search\Provider;
use StatamicRadPack\Runway\Search\Searchable;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_gets_models()
    {
        $posts = Post::factory()->count(5)->create();

        $provider = $this->makeProvider('en', ['searchables' => ['post']]);

        $this->assertEquals([
            "runway::post::{$posts[0]->id}",
            "runway::post::{$posts[1]->id}",
            "runway::post::{$posts[2]->id}",
            "runway::post::{$posts[3]->id}",
            "runway::post::{$posts[4]->id}",
        ], $provider->provide()->all());
    }

    #[Test]
    public function it_filters_out_unpublished_models()
    {
        $publishedModels = Post::factory()->count(2)->create();
        Post::factory()->count(2)->unpublished()->create();

        $provider = $this->makeProvider('en', ['searchables' => ['post']]);

        $this->assertEquals([
            "runway::post::{$publishedModels[0]->id}",
            "runway::post::{$publishedModels[1]->id}",
        ], $provider->provide()->all());
    }

    private function makeProvider($locale, $config)
    {
        $index = $this->makeIndex($locale, $config);

        $keys = $this->normalizeSearchableKeys($config['searchables'] ?? null);

        return (new Provider)->setIndex($index)->setKeys($keys);
    }

    private function makeIndex($locale, $config)
    {
        $index = $this->mock(\Statamic\Search\Index::class);

        $index->shouldReceive('config')->andReturn($config);
        $index->shouldReceive('locale')->andReturn($locale);

        return $index;
    }

    private function normalizeSearchableKeys($keys)
    {
        // a bit of duplicated implementation logic.
        // but it makes the test look more like the real thing.
        return collect($keys === 'all' ? ['*'] : $keys)
            ->map(fn ($key) => str_replace('users:', '', $key))
            ->all();
    }
}
