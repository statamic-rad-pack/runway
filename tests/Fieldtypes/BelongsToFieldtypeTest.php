<?php

namespace DoubleThreeDigital\Runway\Tests\Fieldtypes;

use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Statamic\Fields\Field;
use Statamic\Http\Requests\FilteredRequest;

class BelongsToFieldtypeTest extends TestCase
{
    use WithFaker;

    protected BelongsToFieldtype $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = tap(new BelongsToFieldtype())
            ->setField(new Field('author', [
                'max_items' => 1,
                'mode' => 'stack',
                'resource' => 'author',
                'display' => 'Author',
                'type' => 'belongs_to',
            ]));
    }

    /** @test */
    public function can_get_index_items()
    {
        Author::factory()->count(10)->create();

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
        $authors = Author::factory()->count(2)->create();

        $this->fieldtype->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'default',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
            'title_format' => 'AUTHOR {{ name }}',
        ]));

        $getIndexItems = $this->fieldtype->getIndexItems(new FilteredRequest());

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Paginator);
        $this->assertEquals($getIndexItems->count(), 2);

        $this->assertEquals($getIndexItems->first()['title'], 'AUTHOR '.$authors[0]->name);
        $this->assertEquals($getIndexItems->last()['title'], 'AUTHOR '.$authors[1]->name);
    }

    /** @test */
    public function can_get_index_items_in_order_specified_in_runway_config()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author.order_by', 'name');
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author.order_by_direction', 'desc');

        $authorOne = Author::factory()->create(['name' => 'Scully']);
        $authorTwo = Author::factory()->create(['name' => 'Jake Peralta']);
        $authorThree = Author::factory()->create(['name' => 'Amy Santiago']);

        $getIndexItems = $this->fieldtype->getIndexItems(new FilteredRequest(['paginate' => false]));

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Collection);
        $this->assertEquals($getIndexItems->count(), 3);

        $this->assertEquals($getIndexItems->all()[0]['title'], 'Scully');
        $this->assertEquals($getIndexItems->all()[1]['title'], 'Jake Peralta');
        $this->assertEquals($getIndexItems->all()[2]['title'], 'Amy Santiago');
    }

    /** @test */
    public function can_get_item_array_with_title_format()
    {
        $author = Author::factory()->create();

        $this->fieldtype->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'default',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
            'title_format' => 'AUTHOR {{ name }}',
        ]));

        $item = $this->fieldtype->getItemData([1]);

        $this->assertEquals('AUTHOR '.$author->name, $item->first()['title']);
    }

    /** @test */
    public function can_get_pre_process_index()
    {
        $author = Author::factory()->create();

        $preProcessIndex = $this->fieldtype->preProcessIndex($author->id);

        $this->assertTrue($preProcessIndex instanceof Collection);

        $this->assertEquals($preProcessIndex->first(), [
            'id' => $author->id,
            'title' => $author->name,
            'edit_url' => 'http://localhost/cp/runway/author/1',
        ]);
    }

    /** @test */
    public function can_get_augment_value()
    {
        $author = Author::factory()->create();

        $augment = $this->fieldtype->augment($author->id);

        $this->assertIsArray($augment);
        $this->assertEquals($author->id, $augment['id']->value());
        $this->assertEquals($author->name, $augment['name']->value());
    }

    /**
     * @test
     *
     * Under the hood, this tests the `toItemArray` method.
     */
    public function can_get_item_data()
    {
        $author = Author::factory()->create();

        $getItemData = $this->fieldtype->getItemData($author->id);

        $this->assertIsObject($getItemData);
        $this->assertTrue($getItemData instanceof Collection);

        $this->assertArrayHasKey('id', $getItemData[0]);
        $this->assertArrayHasKey('title', $getItemData[0]);
        $this->assertArrayNotHasKey('created_at', $getItemData[0]);
    }
}
