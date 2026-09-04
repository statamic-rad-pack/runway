<?php

namespace StatamicRadPack\Runway\Tests\Query\Scopes\Filters;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use StatamicRadPack\Runway\Fieldtypes\BelongsToFieldtype;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;
use StatamicRadPack\Runway\Scopes\Fields\Models;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ModelsTest extends TestCase
{
    #[Test]
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

    #[Test]
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

    #[Test]
    public function can_apply_filter_on_nested_field()
    {
        Author::factory()->withPosts(3)->create();

        $author = Author::factory()->create();
        Post::factory()->create(['author_id' => $author->id, 'values' => ['alt_title' => 'Alternative Title']]);

        $blueprint = Blueprint::find('runway::author');
        $blueprint->ensureFieldPrepended('posts', ['type' => 'has_many', 'resource' => 'post']);

        Blueprint::shouldReceive('find')->with('runway::author')->andReturn($blueprint);

        $fieldtype = new HasManyFieldtype;
        $fieldtype->setField(new Field('posts', ['resource' => 'post']));

        $query = Author::query();

        (new Models($fieldtype))->apply($query, 'posts', [
            'field' => 'values_alt_title',
            'operator' => 'like',
            'value' => 'Alternative',
        ]);

        $this->assertEquals([$author->id], $query->pluck('id')->all());
    }

    #[Test]
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
