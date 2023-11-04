<?php

namespace DoubleThreeDigital\Runway\Tests\Tags;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tags\RunwayTag;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Antlers;
use Statamic\Fields\Value;

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

        $this->assertEquals(5, count($usage));

        $this->assertEquals((string) $usage[0]['title'], $posts[0]->title);
        $this->assertEquals((string) $usage[1]['title'], $posts[1]->title);
        $this->assertEquals((string) $usage[2]['title'], $posts[2]->title);
        $this->assertEquals((string) $usage[3]['title'], $posts[3]->title);
        $this->assertEquals((string) $usage[4]['title'], $posts[4]->title);
    }

    /** @test */
    public function can_get_records_with_select_parameter()
    {
        $posts = $this->postFactory(5);

        $this->tag->setParameters([
            'select' => 'id,title,slug',
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertEquals(5, count($usage));

        $this->assertEquals((string) $usage[0]['title']->value(), $posts[0]->title);
        $this->assertEquals((string) $usage[0]['slug']->value(), $posts[0]->slug);
        $this->assertEmpty((string) $usage[0]['body']->value());

        $this->assertEquals((string) $usage[1]['title']->value(), $posts[1]->title);
        $this->assertEquals((string) $usage[1]['slug']->value(), $posts[1]->slug);
        $this->assertEmpty((string) $usage[1]['body']->value());

        $this->assertEquals((string) $usage[2]['title']->value(), $posts[2]->title);
        $this->assertEquals((string) $usage[2]['slug']->value(), $posts[2]->slug);
        $this->assertEmpty((string) $usage[2]['body']->value());

        $this->assertEquals((string) $usage[3]['title']->value(), $posts[3]->title);
        $this->assertEquals((string) $usage[3]['slug']->value(), $posts[3]->slug);
        $this->assertEmpty((string) $usage[3]['body']->value());

        $this->assertEquals((string) $usage[4]['title']->value(), $posts[4]->title);
        $this->assertEquals((string) $usage[4]['slug']->value(), $posts[4]->slug);
        $this->assertEmpty((string) $usage[4]['body']->value());
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

        $this->assertEquals(3, count($usage));
        $this->assertEquals((string) $usage[0]['title']->value(), 'Pasta');
        $this->assertEquals((string) $usage[1]['title']->value(), 'Apple');
        $this->assertEquals((string) $usage[2]['title']->value(), 'Burger');
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

        $this->assertEquals(1, count($usage));
        $this->assertEquals((string) $usage[0]['title']->value(), 'Apple');
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

        $this->assertEquals(1, count($usage));
        $this->assertEquals((string) $usage[0]['title']->value(), 'Apple');
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

        $this->assertEquals(1, count($usage));
        $this->assertEquals((string) $usage[0]['title']->value(), 'penguin');
    }

    /** @test */
    public function can_get_records_with_where_parameter_when_condition_is_on_relationship_field()
    {
        $posts = $this->postFactory(5);
        $author = $this->authorFactory();

        $posts[0]->update(['author_id' => $author->id]);
        $posts[2]->update(['author_id' => $author->id]);
        $posts[3]->update(['author_id' => $author->id]);

        $this->tag->setParameters([
            'where' => 'author_id:'.$author->id,
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertEquals(3, count($usage));
        $this->assertEquals((string) $usage[0]['title']->value(), $posts[0]->title);
        $this->assertEquals((string) $usage[1]['title']->value(), $posts[2]->title);
        $this->assertEquals((string) $usage[2]['title']->value(), $posts[3]->title);
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

        $this->assertEquals(5, count($usage));
        $this->assertEquals((string) $usage[0]['title'], 'tiger');

        $this->assertInstanceOf(Value::class, $usage[0]['author']);
        $this->assertEquals($usage[0]['author']->value()['name']->value(), $posts[0]->author->name);
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

        $this->assertEquals(2, count($usage));

        $this->assertEquals((string) $usage[0]['title'], 'def');
        $this->assertEquals((string) $usage[1]['title'], 'abc');
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

        $this->assertEquals(2, count($usage['items']));

        $this->assertEquals((string) $usage['items'][0]['title'], 'abc');
        $this->assertEquals((string) $usage['items'][1]['title'], 'def');
    }

    /** @test */
    public function can_get_records_with_limit_parameter()
    {
        $posts = $this->postFactory(5);

        $this->tag->setParameters([
            'limit' => 2,
        ]);

        $usage = $this->tag->wildcard('post');

        $this->assertEquals(2, count($usage));

        $this->assertEquals((string) $usage[0]['title'], $posts[0]['title']);
        $this->assertEquals((string) $usage[1]['title'], $posts[1]['title']);

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

        $this->assertEquals(2, count($usage['items']));

        $this->assertEquals((string) $usage['items'][0]['title'], $posts[0]['title']);
        $this->assertEquals((string) $usage['items'][1]['title'], $posts[1]['title']);

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

        $this->assertEquals(2, count($usage));

        $this->assertEquals($usage[0]['id']->value(), $posts[0]['id']);
        $this->assertEquals($usage[1]['id']->value(), $posts[1]['id']);

        $this->assertEquals((string) $usage[0]['title']->value(), $posts[0]['title']);
        $this->assertEquals((string) $usage[1]['title']->value(), $posts[1]['title']);
    }

    /** @test */
    public function can_get_records_with_studly_case_resource_handle()
    {
        Config::set('runway.resources.'.Post::class.'.handle', 'BlogPosts');

        Runway::discoverResources();

        $posts = $this->postFactory(5);

        $this->tag->setParameters([]);
        $usage = $this->tag->wildcard('blog_posts');

        $this->assertEquals(5, count($usage));

        $this->assertEquals((string) $usage[0]['title'], $posts[0]->title);
        $this->assertEquals((string) $usage[1]['title'], $posts[1]->title);
        $this->assertEquals((string) $usage[2]['title'], $posts[2]->title);
        $this->assertEquals((string) $usage[3]['title'], $posts[3]->title);
        $this->assertEquals((string) $usage[4]['title'], $posts[4]->title);
    }
}
