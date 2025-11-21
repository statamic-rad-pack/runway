<?php

namespace StatamicRadPack\Runway\Tests\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Query\Scopes\Scope;
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

    #[Test]
    #[DataProvider('indexFilterProvider')]
    public function it_can_use_a_custom_filter($filter)
    {
        $a = Post::factory()->create();
        $b = Post::factory()->unpublished()->create();
        $c = Post::factory()->create(['title' => 'Not Searchable']);
        $d = Post::factory()->create(['title' => 'Searchable']);
        $e = Post::factory()->create();

        $provider = $this->makeProvider('en', [
            'searchables' => ['post'],
            'filter' => $filter,
        ]);

        $this->assertEquals(
            ["runway::post::{$a->id}", "runway::post::{$b->id}", "runway::post::{$d->id}", "runway::post::{$e->id}"],
            $provider->provide()->all()
        );

        $this->assertTrue($provider->contains(new Searchable($a)));
        $this->assertTrue($provider->contains(new Searchable($b)));
        $this->assertFalse($provider->contains(new Searchable($c)));
        $this->assertTrue($provider->contains(new Searchable($d)));
        $this->assertTrue($provider->contains(new Searchable($e)));
    }

    public static function indexFilterProvider()
    {
        return [
            'class' => [TestSearchableModelsFilter::class],
            'closure' => [
                function ($model) {
                    return $model->title !== 'Not Searchable';
                },
            ],
        ];
    }

    #[Test]
    public function it_can_use_a_query_scope()
    {
        CustomModelsScope::register();

        $a = Post::factory()->create();
        $b = Post::factory()->create();
        $c = Post::factory()->create(['title' => 'Not Searchable']);
        $d = Post::factory()->create(['title' => 'Searchable']);
        $e = Post::factory()->create();

        $provider = $this->makeProvider('en', [
            'searchables' => ['post'],
            'query_scope' => 'custom_models_scope',
        ]);

        $this->assertEquals(
            ["runway::post::{$a->id}", "runway::post::{$b->id}", "runway::post::{$d->id}", "runway::post::{$e->id}"],
            $provider->provide()->all()
        );

        $this->assertTrue($provider->contains(new Searchable($a)));
        $this->assertTrue($provider->contains(new Searchable($b)));
        $this->assertFalse($provider->contains(new Searchable($c)));
        $this->assertTrue($provider->contains(new Searchable($d)));
        $this->assertTrue($provider->contains(new Searchable($e)));
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

class TestSearchableModelsFilter
{
    public function handle($item)
    {
        return $item->title !== 'Not Searchable';
    }
}

class CustomModelsScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', '!=', 'Not Searchable');
    }
}
