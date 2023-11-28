<?php

namespace DoubleThreeDigital\Runway\Tests\Query\Scopes\Filters;

use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use DoubleThreeDigital\Runway\Query\Scopes\Filters\Fields\Models;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Statamic\Fields\Field;

class ModelsTest extends TestCase
{
    /** @test */
    public function it_gets_field_items()
    {
        $fieldtype = new BelongsToFieldtype;
        $fieldtype->setField(new Field('author', ['resource' => 'post']));

        $fieldItems = (new Models($fieldtype))->fieldItems();

        $this->assertIsArray($fieldItems);

        $this->assertArrayHasKey('field', $fieldItems);
        $this->assertArrayHasKey('operator', $fieldItems);
        $this->assertArrayHasKey('value', $fieldItems);

        $this->assertEquals([
            'id' => 'ID',
            'title' => 'Title',
        ], $fieldItems['field']['options']);
    }

    /** @test */
    public function can_apply_filter_on_normal_column()
    {
        Post::factory()->count(5)->create();
        $author = Author::factory()->withPosts(3)->create(['name' => 'David Hasselhoff']);

        $fieldtype = new BelongsToFieldtype;
        $fieldtype->setField(new Field('author_id', ['resource' => 'author']));

        $query = Post::query();

        $apply = (new Models($fieldtype))->apply(
            $query,
            'author_id',
            [
                'field' => 'name',
                'operator' => 'like',
                'value' => 'Hasselhoff',
            ]
        );

        $results = $query->get();

        $this->assertCount(3, $results);
        $this->assertEquals($author->id, $results[0]->author_id);
        $this->assertEquals($author->id, $results[1]->author_id);
        $this->assertEquals($author->id, $results[2]->author_id);
    }

    /** @test */
    public function can_get_badge()
    {
        $fieldtype = new BelongsToFieldtype;
        $fieldtype->setField(new Field('author_id', ['resource' => 'author']));

        $badge = (new Models($fieldtype))->badge([
            'field' => 'name',
            'operator' => 'like',
            'value' => 'Hasselhoff',
        ]);

        $this->assertEquals('Author Id Name contains Hasselhoff', $badge);
    }
}
