<?php

namespace DoubleThreeDigital\Runway\Tests\Fieldtypes;

use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Statamic\Fields\Field;
use Statamic\Http\Requests\FilteredRequest;

class BelongsToFieldtypeTest extends TestCase
{
    use WithFaker;

    protected BelongsToFieldtype $stackFieldtype;

    protected BelongsToFieldtype $selectFieldtype;

    protected BelongsToFieldtype $typeaheadFieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->stackFieldtype = tap(new BelongsToFieldtype())->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'default',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
        ]));
        $this->selectFieldtype = tap(new BelongsToFieldtype())->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'select',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
        ]));
        $this->typeaheadFieldtype = tap(new BelongsToFieldtype())->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'typeahead',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
        ]));
    }

    /** @test */
    public function can_get_index_items()
    {
        $authors = $this->authorFactory(10);

        $getIndexItems = $this->stackFieldtype->getIndexItems(new FilteredRequest());
        $getIndexItems2 = $this->selectFieldtype->getIndexItems(new FilteredRequest());
        $getIndexItems3 = $this->typeaheadFieldtype->getIndexItems(new FilteredRequest());

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Paginator);
        $this->assertSame($getIndexItems->count(), 10);

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
        $authors = $this->authorFactory(2);

        $this->stackFieldtype->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'default',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
            'title_format' => 'AUTHOR {{ name }}',
        ]));

        $getIndexItems = $this->stackFieldtype->getIndexItems(new FilteredRequest());

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Paginator);
        $this->assertSame($getIndexItems->count(), 2);

        $this->assertSame($getIndexItems->first()['title'], 'AUTHOR '.$authors[0]->name);
        $this->assertSame($getIndexItems->last()['title'], 'AUTHOR '.$authors[1]->name);
    }

    /** @test */
    public function can_get_item_array_with_title_format()
    {
        $author = $this->authorFactory();

        $this->stackFieldtype->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'default',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
            'title_format' => 'AUTHOR {{ name }}',
        ]));

        $item = $this->stackFieldtype->getItemData([1]);

        $this->assertSame('AUTHOR '.$author->name, $item->first()['title']);
    }

    /** @test */
    public function can_get_pre_process_index()
    {
        $author = $this->authorFactory();

        $preProcessIndex = $this->stackFieldtype->preProcessIndex($author->id);

        $this->assertTrue($preProcessIndex instanceof Collection);

        $this->assertSame($preProcessIndex->first(), [
            'id' => $author->id,
            'title' => $author->name,
            'edit_url' => 'http://localhost/cp/runway/author/1',
        ]);
    }

    /** @test */
    public function can_get_augment_value()
    {
        $authors = $this->authorFactory(5);

        $augment = $this->stackFieldtype->augment(
            collect($authors)->pluck('id')->toArray()
        );

        $this->assertIsArray($augment);
        $this->assertSame(count($augment), 5);

        $this->assertSame($authors[0]->id, $augment['id']);
        $this->assertSame($authors[0]->name, (string) $augment['name']);
    }

    /**
     * @test
     *
     * Under the hood, this tests the `toItemArray` method.
     */
    public function can_get_item_data()
    {
        $authors = $this->authorFactory(2);

        $getItemData = $this->stackFieldtype->getItemData(
            collect($authors)->pluck('id')->toArray()
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
