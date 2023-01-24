<?php

namespace DoubleThreeDigital\Runway\Tests\Tags;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tags\RunwayTag;
use DoubleThreeDigital\Runway\Tests\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Antlers;

class RunwayTagTest extends TestCase
{
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
        $posts = $this->postFactory(5);

        $this->tag->setParameters([]);
        $usage = $this->tag->wildcard('post');

        $this->assertSame(5, count($usage));

        $this->assertSame((string) $usage[0]['title'], $posts[0]->title);
        $this->assertSame((string) $usage[1]['title'], $posts[1]->title);
        $this->assertSame((string) $usage[2]['title'], $posts[2]->title);
        $this->assertSame((string) $usage[3]['title'], $posts[3]->title);
        $this->assertSame((string) $usage[4]['title'], $posts[4]->title);
    }

    /** @test */
    public function can_get_records_with_scope_parameter()
    {
        $posts = $this->postFactory(5);

        $posts[0]->update(['title' => 'Pasta']);
        $posts[2]->update(['title' => 'Apple']);
        $posts[4]->update(['title' => 'Burger']);

        $this->tag->setParameters([
            'scope' => 'food',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(3, count($usage));
        $this->assertSame((string) $usage[0]['title'], 'Pasta');
        $this->assertSame((string) $usage[1]['title'], 'Apple');
        $this->assertSame((string) $usage[2]['title'], 'Burger');
    }

    /** @test */
    public function can_get_records_with_scope_parameter_and_scope_arguments()
    {
        $posts = $this->postFactory(5);

        $posts[0]->update(['title' => 'Pasta']);
        $posts[2]->update(['title' => 'Apple']);
        $posts[4]->update(['title' => 'Burger']);

        $this->tag->setContext([
            'fab' => 'idoo',
        ]);

        $this->tag->setParameters([
            'scope' => 'fruit:fab',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(1, count($usage));
        $this->assertSame((string) $usage[0]['title'], 'Apple');
    }

    /** @test */
    public function can_get_records_with_scope_parameter_and_scope_arguments_and_multiple_scopes()
    {
        $posts = $this->postFactory(5);

        $posts[0]->update(['title' => 'Pasta']);
        $posts[2]->update(['title' => 'Apple']);
        $posts[4]->update(['title' => 'Burger']);

        $this->tag->setContext([
            'fab' => 'idoo',
        ]);

        $this->tag->setParameters([
            'scope' => 'food|fruit:fab',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(1, count($usage));
        $this->assertSame((string) $usage[0]['title'], 'Apple');
    }

    /** @test */
    public function can_get_records_with_where_parameter()
    {
        $posts = $this->postFactory(5);

        $posts[0]->update(['title' => 'penguin']);

        $this->tag->setParameters([
            'where' => 'title:penguin',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(1, count($usage));
        $this->assertSame((string) $usage[0]['title'], 'penguin');
    }

    /** @test */
    public function can_get_records_with_with_parameter()
    {
        $posts = $this->postFactory(5);

        $posts[0]->update(['title' => 'tiger']);

        $this->tag->setParameters([
            'with' => 'author',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(5, count($usage));
        $this->assertSame((string) $usage[0]['title'], 'tiger');

        $this->assertIsArray($usage[0]['author']);
        $this->assertSame($usage[0]['author']['name'], $posts[0]->author->name);
    }

    /** @test */
    public function can_get_records_with_sort_parameter()
    {
        $posts = $this->postFactory(2);

        $posts[0]->update(['title' => 'abc']);
        $posts[1]->update(['title' => 'def']);

        $this->tag->setParameters([
            'sort' => 'title:desc',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage));

        $this->assertSame((string) $usage[0]['title'], 'def');
        $this->assertSame((string) $usage[1]['title'], 'abc');
    }

    /** @test */
    public function can_get_records_with_scoping()
    {
        $posts = $this->postFactory(2);

        $posts[0]->update(['title' => 'abc']);
        $posts[1]->update(['title' => 'def']);

        $this->tag->setParameters([
            'as' => 'items',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage['items']));

        $this->assertSame((string) $usage['items'][0]['title'], 'abc');
        $this->assertSame((string) $usage['items'][1]['title'], 'def');
    }

    /** @test */
    public function can_get_records_with_limit_parameter()
    {
        $posts = $this->postFactory(5);

        $this->tag->setParameters([
            'limit' => 2,
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage));

        $this->assertSame((string) $usage[0]['title'], $posts[0]['title']);
        $this->assertSame((string) $usage[1]['title'], $posts[1]['title']);

        $this->assertFalse(isset($usage[2]));
    }

    /** @test */
    public function can_get_records_with_scoping_and_pagination()
    {
        $posts = $this->postFactory(5);

        $this->tag->setParameters([
            'limit' => 2,
            'as' => 'items',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage['items']));

        $this->assertSame((string) $usage['items'][0]['title'], $posts[0]['title']);
        $this->assertSame((string) $usage['items'][1]['title'], $posts[1]['title']);

        $this->assertFalse(isset($usage['items'][2]));

        $this->assertArrayHasKey('paginate', $usage);
        $this->assertArrayHasKey('no_results', $usage);
    }

    /** @test */
    public function can_get_records_and_non_blueprint_columns_are_returned()
    {
        $posts = $this->postFactory(2);

        $this->tag->setParameters([]);

        $usage = $this->tag->wildcard('post');

        $this->assertSame(2, count($usage));

        $this->assertSame($usage[0]['id'], $posts[0]['id']);
        $this->assertSame($usage[1]['id'], $posts[1]['id']);

        $this->assertSame((string) $usage[0]['title'], $posts[0]['title']);
        $this->assertSame((string) $usage[1]['title'], $posts[1]['title']);
    }

    /** @test */
    public function can_get_records_with_studly_case_resource_handle()
    {
        Config::set('runway.resources.' . Post::class . '.handle', 'BlogPosts');

        Runway::discoverResources();

        $posts = $this->postFactory(5);

        $this->tag->setParameters([]);
        $usage = $this->tag->wildcard('blog_posts');

        $this->assertSame(5, count($usage));

        $this->assertSame((string) $usage[0]['title'], $posts[0]->title);
        $this->assertSame((string) $usage[1]['title'], $posts[1]->title);
        $this->assertSame((string) $usage[2]['title'], $posts[2]->title);
        $this->assertSame((string) $usage[3]['title'], $posts[3]->title);
        $this->assertSame((string) $usage[4]['title'], $posts[4]->title);
    }
}
