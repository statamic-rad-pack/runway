<?php

namespace DoubleThreeDigital\Runway\Tests\Fieldtypes;

use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Statamic\Fields\Field;

class HasManyFieldtypeTest extends TestCase
{
    use WithFaker;

    protected HasManyFieldtype $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new HasManyFieldtype();

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

        $this->fieldtype->setField(new Field('posts', [
            'mode' => 'default',
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

        $getIndexItems = $this->fieldtype->getIndexItems(new Request());

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Collection);
        $this->assertSame($getIndexItems->count(), 10);
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
    public function can_get_augment_value()
    {
        $posts = $this->postFactory(5);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $augment = $this->fieldtype->augment(
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

    /**
     * @test
     */
    public function it_can_save_model_with_has_many_relation()
    {
        $posts = $this->postFactory(5);
        $author = $this->authorFactory();

        foreach ($posts as $post) {
            $post->update(['author_id' => $author->id]);
        }

        $this->assertTrue($author->update(['name' => 'Foo Bar']));
    }
}
