<?php

namespace StatamicRadPack\Runway\Tests\Search;

use StatamicRadPack\Runway\Search\Provider;
use StatamicRadPack\Runway\Search\Searchable;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ProviderTest extends TestCase
{
    /** @test */
    public function it_gets_models()
    {
        $posts = Post::factory()->count(5)->create();

        $provider = $this->makeProvider('en', ['searchables' => ['post']]);
        $models = $provider->provide();

        $this->assertCount(5, $models);
        $this->assertInstanceOf(Searchable::class, $models[0]);
        $this->assertEquals("runway::post::{$posts[0]->id}", $models[0]->getSearchReference());
        $this->assertInstanceOf(Searchable::class, $models[1]);
        $this->assertEquals("runway::post::{$posts[1]->id}", $models[1]->getSearchReference());
        $this->assertInstanceOf(Searchable::class, $models[2]);
        $this->assertEquals("runway::post::{$posts[2]->id}", $models[2]->getSearchReference());
        $this->assertInstanceOf(Searchable::class, $models[3]);
        $this->assertEquals("runway::post::{$posts[3]->id}", $models[3]->getSearchReference());
        $this->assertInstanceOf(Searchable::class, $models[4]);
        $this->assertEquals("runway::post::{$posts[4]->id}", $models[4]->getSearchReference());
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
