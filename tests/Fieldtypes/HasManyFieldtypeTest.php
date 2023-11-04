<?php

namespace DoubleThreeDigital\Runway\Tests\Fieldtypes;

use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Blink;
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

        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Author.blueprint.sections.main.fields', [
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
        ]);

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
        $posts = $this->postFactory(10);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $getIndexItemsWithPagination = $this->fieldtype->getIndexItems(
            new FilteredRequest(['paginate' => true])
        );

        $getIndexItemsWithoutPagination = $this->fieldtype->getIndexItems(
            new FilteredRequest(['paginate' => false])
        );

        $this->assertIsObject($getIndexItemsWithPagination);
        $this->assertTrue($getIndexItemsWithPagination instanceof Paginator);
        $this->assertSame($getIndexItemsWithPagination->count(), 10);

        $this->assertIsObject($getIndexItemsWithoutPagination);
        $this->assertTrue($getIndexItemsWithoutPagination instanceof Collection);
        $this->assertSame($getIndexItemsWithoutPagination->count(), 10);
    }

    /** @test */
    public function can_get_index_items_with_title_format()
    {
        $posts = $this->postFactory(2);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

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
        $this->assertSame($getIndexItems->count(), 2);

        $this->assertSame($getIndexItems->first()['title'], $posts[0]->title.' TEST '.now()->format('Y'));
        $this->assertSame($getIndexItems->last()['title'], $posts[1]->title.' TEST '.now()->format('Y'));
    }

    /** @test */
    public function can_get_item_array_with_title_format()
    {
        $posts = $this->postFactory(2);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $this->fieldtype->setField(new Field('posts', [
            'mode' => 'default',
            'resource' => 'post',
            'display' => 'Posts',
            'type' => 'has_many',
            'title_format' => '{{ title }} TEST {{ created_at format="Y" }}',
        ]));

        $item = $this->fieldtype->getItemData([$posts[0]->id, $posts[1]->id]);

        $this->assertSame($item->first()['title'], $posts[0]->title.' TEST '.now()->format('Y'));
        $this->assertSame($item->last()['title'], $posts[1]->title.' TEST '.now()->format('Y'));
    }

    /** @test */
    public function can_get_pre_process_index()
    {
        $posts = $this->postFactory(10);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $preProcessIndex = $this->fieldtype->preProcessIndex($author->posts);

        $this->assertTrue($preProcessIndex instanceof Collection);

        $this->assertSame($preProcessIndex->first(), [
            'id' => $posts[0]->id,
            'title' => $posts[0]->title,
            'edit_url' => 'http://localhost/cp/runway/post/'.$posts[0]->id,
        ]);
    }

    /** @test */
    public function can_process_and_add_relations_to_model()
    {
        $posts = $this->postFactory(10);
        $author = $this->authorFactory();

        // Usually these bits would be fetched from the request. However, as we can't mock
        // the request, we're using Blink.
        Blink::put('RunwayRouteResource', 'author');
        Blink::put('RunwayRouteRecord', $author->id);

        $this->fieldtype->process(collect($posts)->pluck('id')->toArray());

        // Ensure the author is attached to all 10 posts
        $this->assertSame($posts[0]->fresh()->author_id, $author->id);
        $this->assertSame($posts[1]->fresh()->author_id, $author->id);
        $this->assertSame($posts[2]->fresh()->author_id, $author->id);
        $this->assertSame($posts[3]->fresh()->author_id, $author->id);
        $this->assertSame($posts[4]->fresh()->author_id, $author->id);
        $this->assertSame($posts[5]->fresh()->author_id, $author->id);
        $this->assertSame($posts[6]->fresh()->author_id, $author->id);
        $this->assertSame($posts[7]->fresh()->author_id, $author->id);
        $this->assertSame($posts[8]->fresh()->author_id, $author->id);
        $this->assertSame($posts[9]->fresh()->author_id, $author->id);
    }

    /** @test */
    public function can_process_and_add_relations_to_model_with_pivot_table()
    {
        $posts = $this->postFactory(3);
        $author = $this->authorFactory();

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
        $posts = $this->postFactory(3);
        $author = $this->authorFactory();

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
        $this->assertSame($posts[0]->fresh()->author_id, $author->id);
        $this->assertSame($posts[1]->fresh()->author_id, $author->id);
        $this->assertSame($posts[2]->fresh()->author_id, $author->id);

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
        $posts = $this->postFactory(3);
        $author = $this->authorFactory();

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
        $posts = $this->postFactory(5);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $augment = $this->fieldtype->augment(
            $author->posts->pluck('id')->toArray()
        );

        $this->assertIsArray($augment);
        $this->assertCount(5, $augment);

        $this->assertSame($posts[0]->id, $augment[0]['id']->value());
        $this->assertSame($posts[0]->title, (string) $augment[0]['title']->value());
    }

    /**
     * @test
     *
     * Under the hood, this tests the `toItemArray` method.
     */
    public function can_get_item_data()
    {
        $posts = $this->postFactory(5);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

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
