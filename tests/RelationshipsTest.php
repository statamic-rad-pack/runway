<?php

namespace StatamicRadPack\Runway\Tests;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as FieldsBlueprint;
use StatamicRadPack\Runway\Relationships;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;

class RelationshipsTest extends TestCase
{
    #[Test]
    public function can_add_models_when_saving_has_many_relationship()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(10)->create();

        Blueprint::shouldReceive('find')->with('runway::post')->andReturn(new FieldsBlueprint);

        Blueprint::shouldReceive('find')
            ->with('runway::author')
            ->andReturn((new FieldsBlueprint)->setContents([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'posts', 'field' => ['type' => 'has_many', 'resource' => 'post']],
                        ],
                    ],
                ],
            ]));

        Relationships::for($author)->with(['posts' => $posts->pluck('id')->all()])->save();

        $this->assertTrue(
            $posts->every(fn ($post) => $post->fresh()->author_id === $author->id)
        );
    }

    #[Test]
    public function can_delete_models_when_saving_has_many_relationship()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create(['author_id' => $author->id]);

        Blueprint::shouldReceive('find')->with('runway::post')->andReturn(new FieldsBlueprint);

        Blueprint::shouldReceive('find')
            ->with('runway::author')
            ->andReturn((new FieldsBlueprint)->setContents([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'posts', 'field' => ['type' => 'has_many', 'resource' => 'post']],
                        ],
                    ],
                ],
            ]));

        Relationships::for($author)->with(['posts' => [
            $posts[1]->id,
            $posts[2]->id,
        ]])->save();

        $this->assertDatabaseMissing('posts', ['id' => $posts[0]->id]);
        $this->assertDatabaseHas('posts', ['id' => $posts[1]->id, 'author_id' => $author->id]);
        $this->assertDatabaseHas('posts', ['id' => $posts[2]->id, 'author_id' => $author->id]);
    }

    #[Test]
    public function can_update_sort_orders_when_saving_has_many_relationship()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create();

        Blueprint::shouldReceive('find')->with('runway::post')->andReturn(new FieldsBlueprint);

        Blueprint::shouldReceive('find')
            ->with('runway::author')
            ->andReturn((new FieldsBlueprint)->setContents([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'posts', 'field' => ['type' => 'has_many', 'resource' => 'post', 'reorderable' => true, 'order_column' => 'sort_order']],
                        ],
                    ],
                ],
            ]));

        Relationships::for($author)->with(['posts' => [
            $posts[1]->id,
            $posts[2]->id,
            $posts[0]->id,
        ]])->save();

        $this->assertDatabaseHas('posts', ['id' => $posts[0]->id, 'author_id' => $author->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('posts', ['id' => $posts[1]->id, 'author_id' => $author->id, 'sort_order' => 0]);
        $this->assertDatabaseHas('posts', ['id' => $posts[2]->id, 'author_id' => $author->id, 'sort_order' => 1]);
    }

    #[Test]
    public function can_add_models_when_saving_belongs_to_many_relationship()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create();

        Blueprint::shouldReceive('find')
            ->with('runway::author')
            ->andReturn((new FieldsBlueprint)->setContents([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'pivottedPosts', 'field' => ['type' => 'has_many', 'resource' => 'post']],
                        ],
                    ],
                ],
            ]));

        Relationships::for($author)->with(['pivottedPosts' => $posts->pluck('id')->all()])->save();

        $this->assertDatabaseHas('post_author', ['post_id' => $posts[0]->id, 'author_id' => $author->id]);
        $this->assertDatabaseHas('post_author', ['post_id' => $posts[1]->id, 'author_id' => $author->id]);
        $this->assertDatabaseHas('post_author', ['post_id' => $posts[2]->id, 'author_id' => $author->id]);
    }

    #[Test]
    public function can_remove_models_when_saving_belongs_to_many_relationship()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create();

        Blueprint::shouldReceive('find')
            ->with('runway::author')
            ->andReturn((new FieldsBlueprint)->setContents([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'pivottedPosts', 'field' => ['type' => 'has_many', 'resource' => 'post']],
                        ],
                    ],
                ],
            ]));

        Relationships::for($author)->with(['pivottedPosts' => [
            $posts[1]->id,
            $posts[2]->id,
        ]])->save();

        $this->assertDatabaseMissing('post_author', ['post_id' => $posts[0]->id, 'author_id' => $author->id]);
        $this->assertDatabaseHas('post_author', ['post_id' => $posts[1]->id, 'author_id' => $author->id]);
        $this->assertDatabaseHas('post_author', ['post_id' => $posts[2]->id, 'author_id' => $author->id]);
    }

    #[Test]
    public function can_update_sort_orders_when_saving_belongs_to_relationship()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(3)->create();

        Blueprint::shouldReceive('find')
            ->with('runway::author')
            ->andReturn((new FieldsBlueprint)->setContents([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'pivottedPosts', 'field' => ['type' => 'has_many', 'resource' => 'post', 'reorderable' => true, 'order_column' => 'pivot_sort_order']],
                        ],
                    ],
                ],
            ]));

        Relationships::for($author)->with(['pivottedPosts' => [
            $posts[0]->id,
            $posts[2]->id,
            $posts[1]->id,
        ]])->save();

        $this->assertDatabaseHas('post_author', ['post_id' => $posts[0]->id, 'author_id' => $author->id, 'pivot_sort_order' => 0]);
        $this->assertDatabaseHas('post_author', ['post_id' => $posts[1]->id, 'author_id' => $author->id, 'pivot_sort_order' => 2]);
        $this->assertDatabaseHas('post_author', ['post_id' => $posts[2]->id, 'author_id' => $author->id, 'pivot_sort_order' => 1]);
    }

    #[Test]
    public function does_not_attempt_to_save_computed_fields()
    {
        $author = Author::factory()->create();
        $posts = Post::factory()->count(10)->create();

        Blueprint::shouldReceive('find')->with('runway::post')->andReturn(new FieldsBlueprint);

        Blueprint::shouldReceive('find')
            ->with('runway::author')
            ->andReturn((new FieldsBlueprint)->setContents([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'posts', 'field' => ['type' => 'has_many', 'resource' => 'post', 'visibility' => 'computed', 'save' => false]],
                        ],
                    ],
                ],
            ]));

        Relationships::for($author)->with(['posts' => $posts->pluck('id')->all()])->save();

        $this->assertFalse(
            $posts->every(fn ($post) => $post->fresh()->author_id === $author->id)
        );
    }
}
