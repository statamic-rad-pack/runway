<?php

namespace DoubleThreeDigital\Runway\Tests\Fieldtypes;

use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Blink;
use Statamic\Fields\Field;

class HasManyFieldtypeTest extends TestCase
{
    use WithFaker;

    protected HasManyFieldtype $fieldtype;

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
        ]);

        $this->stackFieldtype = tap(new HasManyFieldtype())
            ->setField(new Field('posts', [
                'mode' => 'stack',
                'resource' => 'post',
                'display' => 'Posts',
                'type' => 'has_many',
            ]));
        $this->selectFieldtype = tap(new HasManyFieldtype())
            ->setField(new Field('posts', [
                'mode' => 'select',
                'resource' => 'post',
                'display' => 'Posts',
                'type' => 'has_many',
            ]));
        $this->typeaheadFieldtype = tap(new HasManyFieldtype())
            ->setField(new Field('posts', [
                'mode' => 'typeahead',
                'resource' => 'post',
                'display' => 'Posts',
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

        $getIndexItems1 = $this->stackFieldtype->getIndexItems(new Request());
        $getIndexItems2 = $this->selectFieldtype->getIndexItems(new Request());
        $getIndexItems3 = $this->typeaheadFieldtype->getIndexItems(new Request());

        $this->assertIsObject($getIndexItems1);
        $this->assertTrue($getIndexItems1 instanceof Paginator);
        $this->assertSame($getIndexItems1->count(), 10);

        $this->assertIsObject($getIndexItems2);
        $this->assertTrue($getIndexItems2 instanceof Collection);
        $this->assertSame($getIndexItems2->count(), 10);

        $this->assertIsObject($getIndexItems3);
        $this->assertTrue($getIndexItems3 instanceof Collection);
        $this->assertSame($getIndexItems3->count(), 10);
    }

    /** @test */
    public function can_get_index_items_with_title_format()
    {
        $posts = $this->postFactory(2);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $this->stackFieldtype->setField(new Field('posts', [
            'mode' => 'default',
            'resource' => 'post',
            'display' => 'Posts',
            'type' => 'has_many',
            'title_format' => '{{ title }} TEST {{ created_at format="Y" }}',
        ]));

        $getIndexItems = $this->stackFieldtype->getIndexItems(new Request());

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

        $this->stackFieldtype->setField(new Field('posts', [
            'mode' => 'default',
            'resource' => 'post',
            'display' => 'Posts',
            'type' => 'has_many',
            'title_format' => '{{ title }} TEST {{ created_at format="Y" }}',
        ]));

        $item = $this->stackFieldtype->getItemData([$posts[0]->id, $posts[1]->id]);

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

        $preProcessIndex = $this->stackFieldtype->preProcessIndex($author->posts);

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

        $this->stackFieldtype->process(collect($posts)->pluck('id')->toArray());

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
        public function can_process_and_add_relations_to_model_and_can_persist_users_sort_order()
        {
            $posts = $this->postFactory(3);
            $author = $this->authorFactory();

            $this->stackFieldtype->field()->setConfig(array_merge($this->stackFieldtype->field()->config(), [
                'reorderable' => true,
                'order_column' => 'sort_order',
            ]));

            // Usually these bits would be fetched from the request. However, as we can't mock
            // the request, we're using Blink.
            Blink::put('RunwayRouteResource', 'author');
            Blink::put('RunwayRouteRecord', $author->id);

            $this->stackFieldtype->process([
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

    /** @test */
    public function can_get_augment_value()
    {
        $posts = $this->postFactory(5);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $augment = $this->stackFieldtype->augment(
            $author->posts
        );

        $this->assertIsArray($augment);
        $this->assertSame(count($augment), 5);

        $this->assertSame($posts[0]->id, $augment[0]['id']);
        $this->assertSame($posts[0]->title, (string) $augment[0]['title']);
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

        $getItemData = $this->stackFieldtype->getItemData(
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
