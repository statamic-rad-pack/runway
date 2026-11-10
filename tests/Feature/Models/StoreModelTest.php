<?php

namespace StatamicRadPack\Runway\Tests\Feature\Models;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Config;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class StoreModelTest extends TestCase
{
    #[Test]
    public function can_store_resource()
    {
        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'published' => true,
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $this->assertDatabaseHas('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    #[Test]
    public function cant_store_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    #[Test]
    public function cant_store_resource_when_blueprint_is_hidden()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->setHidden(true));

        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    #[Test]
    public function can_store_resource_and_ensure_computed_field_isnt_saved_to_database()
    {
        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'published' => true,
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
                'age' => 25, // This is the computed field
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $this->assertDatabaseHas('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    #[Test]
    public function can_store_resource_and_ensure_field_isnt_saved_to_database()
    {
        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'published' => true,
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
                'dont_save' => 25,
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $this->assertDatabaseHas('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    #[Test]
    public function can_store_resource_and_ensure_appended_attribute_doesnt_attempt_to_get_saved()
    {
        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'published' => true,
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'excerpt' => 'This is an excerpt.',
                'author_id' => [$author->id],
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $this->assertDatabaseHas('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    #[Test]
    public function can_store_resource_with_nested_field()
    {
        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'published' => true,
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'values_alt_title' => 'Batman Smells',
                'author_id' => [$author->id],
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $this->assertDatabaseHas('posts', [
            'values->alt_title' => 'Batman Smells',
        ]);
    }

    #[Test]
    public function can_store_resource_and_ensure_date_comparison_validation_works()
    {
        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
                'start_date' => '2025-09-01T00:00:00.000Z',
                'end_date' => '2023-08-01T00:00:00.000Z',
            ])
            ->assertSessionHasErrors('start_date');

        $this->assertDatabaseMissing('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    #[Test]
    public function can_store_resource_with_required_if_published_validation()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->ensureField('membership_status', [
            'type' => 'select',
            'options' => ['free' => 'Free', 'paid' => 'Paid'],
            'validate' => ['required_if:published,true'],
        ]));
        Blueprint::shouldReceive('getAdditionalNamespaces')->andReturn(collect(['runway' => base_path('resources/blueprints/runway')]))->zeroOrMoreTimes();

        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        // Publishing without the required field should fail
        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'published' => true,
                'title' => 'Test Post',
                'slug' => 'test-post',
                'body' => 'Test body',
                'author_id' => [$author->id],
                'membership_status' => null,
            ])
            ->assertSessionHasErrors('membership_status');

        $this->assertDatabaseMissing('posts', [
            'title' => 'Test Post',
        ]);
    }

    #[Test]
    public function can_store_resource_as_draft_without_required_if_published_field()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->ensureField('membership_status', [
            'type' => 'select',
            'options' => ['free' => 'Free', 'paid' => 'Paid'],
            'validate' => ['required_if:published,true'],
        ]));
        Blueprint::shouldReceive('getAdditionalNamespaces')->andReturn(collect(['runway' => base_path('resources/blueprints/runway')]))->zeroOrMoreTimes();

        $author = Author::factory()->create();
        $user = User::make()->makeSuper()->save();

        // Saving as draft without the required field should succeed
        $this
            ->actingAs($user)
            ->post(cp_route('runway.store', ['resource' => 'post']), [
                'published' => false,
                'title' => 'Draft Post',
                'slug' => 'draft-post',
                'body' => 'Draft body',
                'author_id' => [$author->id],
                'membership_status' => null,
            ])
            ->assertOk();

        $this->assertDatabaseHas('posts', [
            'title' => 'Draft Post',
            'published' => false,
        ]);
    }
}
