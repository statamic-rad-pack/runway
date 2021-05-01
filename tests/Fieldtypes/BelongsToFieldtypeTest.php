<?php

namespace DoubleThreeDigital\Runway\Tests\Fieldtypes;

use DoubleThreeDigital\Runway\Tests\Post;
use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use DoubleThreeDigital\Runway\Tests\Author;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Fields\Field;

class BelongsToFieldtypeTest extends TestCase
{
    use WithFaker;

    protected BelongsToFieldtype $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new BelongsToFieldtype();

        $this->fieldtype->setField(new Field('author', [
            'max_items' => 1,
            'mode' => 'default',
            'resource' => 'author',
            'display' => 'Author',
            'type' => 'belongs_to',
        ]));
    }

    /** @test */
    public function can_get_index_items()
    {
        $this->authorFactory(10);

        $getIndexItems = $this->fieldtype->getIndexItems(new Request());

        $this->assertIsObject($getIndexItems);
        $this->assertTrue($getIndexItems instanceof Collection);
        $this->assertSame($getIndexItems->count(), 10);
    }

    /** @test */
    public function can_get_pre_process_index()
    {
        $authors = $this->authorFactory(5);

        $preProcessIndex = $this->fieldtype->preProcessIndex(
            collect($authors)->pluck('id')->toArray()
        );

        $this->assertIsString($preProcessIndex);

        $this->assertStringContainsString($authors[0]->name, $preProcessIndex);
        $this->assertStringContainsString($authors[1]->name, $preProcessIndex);
        $this->assertStringContainsString($authors[2]->name, $preProcessIndex);
        $this->assertStringContainsString($authors[3]->name, $preProcessIndex);
        $this->assertStringContainsString($authors[4]->name, $preProcessIndex);
    }

    /** @test */
    public function can_get_augment_value()
    {
        $authors = $this->authorFactory(5);

        $augment = $this->fieldtype->augment(
            collect($authors)->pluck('id')->toArray()
        );

        $this->assertIsArray($augment);
        $this->assertSame(count($augment), 5);

        $this->assertSame($authors[0]->id, $augment[0]['id']);
        $this->assertSame($authors[0]->name, $augment[0]['name']);

        $this->assertSame($authors[2]->id, $augment[2]['id']);
        $this->assertSame($authors[2]->name, $augment[2]['name']);

        $this->assertSame($authors[4]->id, $augment[4]['id']);
        $this->assertSame($authors[4]->name, $augment[4]['name']);
    }

    /**
     * @test
     *
     * Under the hood, this tests the `toItemArray` method.
     */
    public function can_get_item_data()
    {
        $authors = $this->authorFactory(2);

        $getItemData = $this->fieldtype->getItemData(
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
