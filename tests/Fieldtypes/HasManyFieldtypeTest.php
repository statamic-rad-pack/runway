<?php

namespace DoubleThreeDigital\Runway\Tests\Fieldtypes;

use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use Statamic\Http\Requests\FilteredRequest;

class HasManyFieldtypeTest extends TestCase
{
    use WithFaker;

    protected HasManyFieldtype $fieldtype;

    protected HasManyFieldtype $fieldtypeUsingPivotTable;

    public function setUp(): void
    {
        parent::setUp();

        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->ensureFieldsInTab([
            [
                'handle' => 'name',
                'field' => [
                    'type' => 'text',
                ],
            ],
            [
                'handle' => 'posts',
                'field' => [
                    'type' => 'has_many',
                    'resource' => 'post',
                    'mode' => 'select',
                ],
            ],
            [
                'handle' => 'pivottedPosts',
                'field' => [
                    'type' => 'has_many',
                    'resource' => 'post',
                    'mode' => 'select',
                ],
            ],
        ], 'main'));

        $this->fieldtype = tap(new HasManyFieldtype())
            ->setField(new Field('posts', [
                'mode' => 'stack',
                'resource' => 'post',
                'display' => 'Posts',
                'type' => 'has_many',
            ]));

        $this->fieldtypeUsingPivotTable = tap(new HasManyFieldtype())
            ->setField(new Field('pivottedPosts', [
                'mode' => 'stack',
                'resource' => 'post',
                'display' => 'Pivotted Posts',
                'type' => 'has_many',
            ]));
    }

    /** @test */
    public function can_get_index_items()
    {
        $author = Author::factory()->create();
        Post::factory()->count(10)->create(['author_id' => $author->id]);

        $getIndexItemsWithPagination = $this->fieldtype->getIndexItems(
            new FilteredRequest(['paginate' => true])
        );

        $getIndexItemsWithoutPagination = $this->fieldtype->getIndexItems(
            new FilteredRequest(['paginate' => false])
        );

        $this->assertIsObject($getIndexItemsWithPagination);
        $this->assertTrue($getIndexItemsWithPagination instanceof Paginator);
        $this->assertEquals($getIndexItemsWithPagination->count(), 10);

        $this->assertIsObject($getIndexItemsWithoutPagination);
        $this->assertTrue($getIndexItemsWithoutPagination instanceof Collection);
        $this->assertEquals($getIndexItemsWithoutPagination->count(), 10);
    }

    /** @test */
    public function can_get_index_items_with_title_format()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(2)->create(['author_id' => $author->id]);

        $this->fieldtype->setField(new Field('posts', [
            'mode' => 'default',
            'resource' => 'post',
            'display' => 'Posts',
            'type' => 'has_many',
            'title_format' => '{{ title }} TEST {{ created_at format="Y" }}',
        ]));

        $getIndexItems = $this->fieldtype->getIndexItems(new FilteredRequest());

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Paginator);
        $this->assertEquals($getIndexItems->count(), 2);

        $this->assertEquals($getIndexItems->first()['title'], $posts[0]->title.' TEST '.now()->format('Y'));
        $this->assertEquals($getIndexItems->last()['title'], $posts[1]->title.' TEST '.now()->format('Y'));
    }

    /** @test */
    public function can_get_index_items_in_order_specified_in_runway_config()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post.order_by', 'title');
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post.order_by_direction', 'asc');

        Runway::discoverResources();

        Post::factory()->create(['title' => 'Arnold A']);
        Post::factory()->create(['title' => 'Richard B']);
        Post::factory()->create(['title' => 'Graham C']);

        $getIndexItems = $this->fieldtype->getIndexItems(new FilteredRequest(['paginate' => false]));

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Collection);
        $this->assertEquals($getIndexItems->count(), 3);

        $this->assertEquals($getIndexItems->all()[0]['title'], 'Arnold A');
        $this->assertEquals($getIndexItems->all()[1]['title'], 'Graham C');
        $this->assertEquals($getIndexItems->all()[2]['title'], 'Richard B');
    }

    /** @test */
    public function can_get_index_items_and_search()
    {
        $author = Author::factory()->create();
        Post::factory()->count(10)->create(['author_id' => $author->id]);
        $spacePandaPosts = Post::factory()->count(3)->create(['author_id' => $author->id, 'title' => 'Space Pandas']);

        $getIndexItems = $this->fieldtype->getIndexItems(
            new FilteredRequest(['search' => 'space pan'])
        );

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Paginator);
        $this->assertEquals($getIndexItems->count(), 3);

        $this->assertEquals($getIndexItems->first()['title'], $spacePandaPosts[0]->title);
        $this->assertEquals($getIndexItems->last()['title'], $spacePandaPosts[1]->title);
        $this->assertEquals($getIndexItems->last()['title'], $spacePandaPosts[2]->title);
    }

    /** @test */
    public function can_get_item_array_with_title_format()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(2)->create(['author_id' => $author->id]);

        $this->fieldtype->setField(new Field('posts', [
            'mode' => 'default',
            'resource' => 'post',
            'display' => 'Posts',
            'type' => 'has_many',
            'title_format' => '{{ title }} TEST {{ created_at format="Y" }}',
        ]));

        $item = $this->fieldtype->getItemData([$posts[0]->id, $posts[1]->id]);

        $this->assertEquals($item->first()['title'], $posts[0]->title.' TEST '.now()->format('Y'));
        $this->assertEquals($item->last()['title'], $posts[1]->title.' TEST '.now()->format('Y'));
    }

    /** @test */
    public function can_get_pre_process_index()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(10)->create(['author_id' => $author->id]);

        $preProcessIndex = $this->fieldtype->preProcessIndex($author->posts);

        $this->assertTrue($preProcessIndex instanceof Collection);

        $this->assertEquals($preProcessIndex->first(), [
            'id' => $posts[0]->id,
            'title' => $posts[0]->title,
            'edit_url' => 'http://localhost/cp/runway/post/'.$posts[0]->id,
        ]);
    }

    /** @test */
    public function can_process_and_add_relations_to_model()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(10)->create();

        // Usually these bits would be fetched from the request. However, as we can't mock
        // the request, we're using Blink.
        Blink::put('RunwayRouteResource', 'author');
        Blink::put('RunwayRouteRecord', $author->id);

        $this->fieldtype->process(collect($posts)->pluck('id')->toArray());

        // Ensure the author is attached to all 10 posts
        $this->assertEquals($posts[0]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[1]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[2]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[3]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[4]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[5]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[6]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[7]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[8]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[9]->fresh()->author_id, $author->id);
    }

    /** @test */
    public function can_process_and_add_relations_to_model_with_pivot_table()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create();

        // Usually these bits would be fetched from the request. However, as we can't mock
        // the request, we're using Blink.
        Blink::put('RunwayRouteResource', 'author');
        Blink::put('RunwayRouteRecord', $author->id);

        $this->fieldtypeUsingPivotTable->process([
            $posts[0]->id,
            $posts[1]->id,
            $posts[2]->id,
        ]);

        // Ensure the author is attached to all 3 posts AND the pivot_sort_order is persisted.
        $this->assertDatabaseHas('post_author', [
            'post_id' => $posts[0]->id,
            'author_id' => $author->id,
        ]);

        $this->assertDatabaseHas('post_author', [
            'post_id' => $posts[1]->id,
            'author_id' => $author->id,
        ]);

        $this->assertDatabaseHas('post_author', [
            'post_id' => $posts[2]->id,
            'author_id' => $author->id,
        ]);
    }

    /** @test */
    public function can_process_and_add_relations_to_model_and_can_persist_users_sort_order()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create();

        $this->fieldtype->field()->setConfig(array_merge($this->fieldtype->field()->config(), [
            'reorderable' => true,
            'order_column' => 'sort_order',
        ]));

        // Usually these bits would be fetched from the request. However, as we can't mock
        // the request, we're using Blink.
        Blink::put('RunwayRouteResource', 'author');
        Blink::put('RunwayRouteRecord', $author->id);

        $this->fieldtype->process([
            $posts[1]->id,
            $posts[2]->id,
            $posts[0]->id,
        ]);

        // Ensure the author is attached to all 3 posts
        $this->assertEquals($posts[0]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[1]->fresh()->author_id, $author->id);
        $this->assertEquals($posts[2]->fresh()->author_id, $author->id);

        // Ensure the sort_order is persisted correctly for all 3 posts
        $this->assertDatabaseHas('posts', [
            'id' => $posts[0]->id,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $posts[1]->id,
            'sort_order' => 0,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $posts[2]->id,
            'sort_order' => 1,
        ]);
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/issues/287
     */
    public function can_process_and_add_relations_to_model_and_can_persist_users_sort_order_on_pivot_table()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create();

        $this->fieldtypeUsingPivotTable->field()->setConfig(array_merge($this->fieldtypeUsingPivotTable->field()->config(), [
            'reorderable' => true,
            'order_column' => 'pivot_sort_order',
        ]));

        // Usually these bits would be fetched from the request. However, as we can't mock
        // the request, we're using Blink.
        Blink::put('RunwayRouteResource', 'author');
        Blink::put('RunwayRouteRecord', $author->id);

        $this->fieldtypeUsingPivotTable->process([
            $posts[1]->id,
            $posts[2]->id,
            $posts[0]->id,
        ]);

        // Ensure the author is attached to all 3 posts AND the pivot_sort_order is persisted.
        $this->assertDatabaseHas('post_author', [
            'post_id' => $posts[1]->id,
            'author_id' => $author->id,
            'pivot_sort_order' => 0,
        ]);

        $this->assertDatabaseHas('post_author', [
            'post_id' => $posts[2]->id,
            'author_id' => $author->id,
            'pivot_sort_order' => 1,
        ]);

        $this->assertDatabaseHas('post_author', [
            'post_id' => $posts[0]->id,
            'author_id' => $author->id,
            'pivot_sort_order' => 2,
        ]);
    }

    /** @test */
    public function can_get_augment_value()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(5)->create(['author_id' => $author->id]);

        $augment = $this->fieldtype->augment(
            $author->posts->pluck('id')->toArray()
        );

        $this->assertIsArray($augment);
        $this->assertCount(5, $augment);

        $this->assertEquals($posts[0]->id, $augment[0]['id']->value());
        $this->assertEquals($posts[0]->title, (string) $augment[0]['title']->value());
    }

    /**
     * @test
     *
     * Under the hood, this tests the `toItemArray` method.
     */
    public function can_get_item_data()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(5)->create(['author_id' => $author->id]);

        $getItemData = $this->fieldtype->getItemData(
            $author->posts
        );

        $this->assertIsObject($getItemData);
        $this->assertTrue($getItemData instanceof Collection);

        $this->assertArrayHasKey('id', $getItemData[0]);
        $this->assertArrayHasKey('title', $getItemData[0]);
        $this->assertArrayNotHasKey('created_at', $getItemData[0]);

        $this->assertArrayHasKey('id', $getItemData[1]);
        $this->assertArrayHasKey('title', $getItemData[1]);
        $this->assertArrayNotHasKey('created_at', $getItemData[1]);
    }
}
