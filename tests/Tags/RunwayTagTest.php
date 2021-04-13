<?php

namespace DoubleThreeDigital\Runway\Tests\Tags;

use DoubleThreeDigital\Runway\Tags\RunwayTag;
use DoubleThreeDigital\Runway\Tests\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Statamic\Facades\Antlers;

class RunwayTagTest extends TestCase
{
    use WithFaker;

    public $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(RunwayTag::class)
            ->setParser(Antlers::parser())
            ->setContext([]);
    }

    /** @test */
    public function has_been_registered()
    {
        $this->assertTrue(isset(app()['statamic.tags']['runway']));
    }

    /** @test */
    public function can_get_records_with_no_parameters()
    {
        $posts = [
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
        ];

        $this->tag->setParameters([]);
        $usage = $this->tag->wildcard('post');

        $this->assertSame(5, count($usage));

        $this->assertSame($usage[0]['title'], $posts[0]->title);
        $this->assertSame($usage[1]['title'], $posts[1]->title);
        $this->assertSame($usage[2]['title'], $posts[2]->title);
        $this->assertSame($usage[3]['title'], $posts[3]->title);
        $this->assertSame($usage[4]['title'], $posts[4]->title);
    }

    /** @test */
    public function can_get_records_with_where_parameter()
    {
        $posts = [
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
        ];

        $posts[0]->update(['title' => 'penguin']);

        $this->tag->setParameters([
            'where' => 'title:penguin',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(1, count($usage));
        $this->assertSame($usage[0]['title'], 'penguin');
    }

    /** @test */
    public function can_get_records_with_sort_parameter()
    {
        $posts = [
            $this->makeFactory(),
            $this->makeFactory(),
        ];

        $posts[0]->update(['title' => 'abc']);
        $posts[1]->update(['title' => 'def']);

        $this->tag->setParameters([
            'sort' => 'title:desc',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage));

        $this->assertSame($usage[0]['title'], 'def');
        $this->assertSame($usage[1]['title'], 'abc');
    }

    /** @test */
    public function can_get_records_with_scoping()
    {
        $posts = [
            $this->makeFactory(),
            $this->makeFactory(),
        ];

        $posts[0]->update(['title' => 'abc']);
        $posts[1]->update(['title' => 'def']);

        $this->tag->setParameters([
            'as' => 'items',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage['items']));

        $this->assertSame($usage['items'][0]['title'], 'abc');
        $this->assertSame($usage['items'][1]['title'], 'def');
    }

    /** @test */
    public function can_get_records_with_limit_parameter()
    {
        $posts = [
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
        ];

        $this->tag->setParameters([
            'limit' => 2,
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage));

        $this->assertSame($usage[0]['title'], $posts[0]['title']);
        $this->assertSame($usage[1]['title'], $posts[1]['title']);

        $this->assertFalse(isset($usage[2]));
    }

    /** @test */
    public function can_get_records_with_scoping_and_pagination()
    {
        $posts = [
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
            $this->makeFactory(),
        ];

        $this->tag->setParameters([
            'limit' => 2,
            'as' => 'items',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage['items']));

        $this->assertSame($usage['items'][0]['title'], $posts[0]['title']);
        $this->assertSame($usage['items'][1]['title'], $posts[1]['title']);

        $this->assertFalse(isset($usage['items'][2]));

        $this->assertArrayHasKey('paginate', $usage);
        $this->assertArrayHasKey('no_results', $usage);
    }

    /** @test */
    public function can_get_records_and_non_blueprint_columns_are_returned()
    {
        $posts = [
            $this->makeFactory(),
            $this->makeFactory(),
        ];

        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage));

        $this->assertSame($usage[0]['id'], $posts[0]['id']);
        $this->assertSame($usage[1]['id'], $posts[1]['id']);

        $this->assertSame($usage[0]['title'], $posts[0]['title']);
        $this->assertSame($usage[1]['title'], $posts[1]['title']);
    }

    protected function makeFactory()
    {
        return Post::create([
            'title' => join(' ', $this->faker->words(6)),
            'body' => join(' ', $this->faker->paragraphs(10)),
        ]);
    }
}
