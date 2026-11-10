<?php

namespace StatamicRadPack\Runway\Tests\Feature\Models;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Config;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\Fixtures\Models\User as UserModel;
use StatamicRadPack\Runway\Tests\TestCase;

class UpdateModelTest extends TestCase
{
    #[Test]
    public function can_update_resource()
    {
        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'published' => true,
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $post->refresh();

        $this->assertEquals($post->title, 'Santa is coming home');
    }

    #[Test]
    public function cant_update_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
            ])
            ->assertRedirect();

        $post->refresh();

        $this->assertNotSame($post->title, 'Santa is coming home');
    }

    #[Test]
    public function cant_update_resource_with_only_view_permission()
    {
        $post = Post::factory()->create();

        Role::make('test')->addPermission('access cp')->addPermission('view post')->save();
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
            ])
            ->assertRedirect();

        $post->refresh();

        $this->assertNotSame($post->title, 'Santa is coming home');
    }

    #[Test]
    public function can_update_resource_and_ensure_computed_field_isnt_saved_to_database()
    {
        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'published' => true,
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
                'age' => 19, // This is the computed field
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $post->refresh();

        $this->assertEquals($post->title, 'Santa is coming home');
    }

    #[Test]
    public function can_update_resource_and_ensure_read_only_timestamp_field_doesnt_prevent_timestamp_update()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->ensureField('updated_at', [
            'type' => 'date',
            'time_enabled' => true,
            'visibility' => 'read_only',
        ]));
        Blueprint::shouldReceive('getAdditionalNamespaces')->andReturn(collect(['runway' => base_path('resources/blueprints/runway')]))->zeroOrMoreTimes();

        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $originalUpdatedAt = $post->updated_at;

        $this->travel(5)->minutes();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'published' => true,
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
                'updated_at' => $originalUpdatedAt->toIso8601ZuluString('millisecond'),
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $post->refresh();

        $this->assertEquals('Santa is coming home', $post->title);
        $this->assertTrue($post->updated_at->gt($originalUpdatedAt));
    }

    #[Test]
    public function can_update_resource_and_ensure_field_with_save_false_isnt_saved_to_database()
    {
        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'published' => true,
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
                'dont_save' => 19,
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $post->refresh();

        $this->assertEquals($post->title, 'Santa is coming home');
    }

    #[Test]
    public function can_update_resource_and_ensure_appended_attribute_doesnt_attempt_to_get_saved()
    {
        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'published' => true,
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'excerpt' => 'This is an excerpt.',
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $post->refresh();

        $this->assertEquals($post->title, 'Santa is coming home');
    }

    #[Test]
    public function can_update_resource_with_nested_field()
    {
        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'published' => true,
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'values_alt_title' => 'Claus is venturing out',
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $post->refresh();

        $this->assertEquals($post->values['alt_title'], 'Claus is venturing out');
    }

    #[Test]
    public function can_update_resource_if_model_is_user_model()
    {
        Config::set('auth.providers.users.model', UserModel::class);

        $user = UserModel::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        UserGroup::make('admins')->title('Admins')->save();
        Role::make('developer')->title('Developer')->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('runway.update', ['resource' => 'user', 'model' => $user->id]), [
                'name' => 'Jane Doe',
                'email' => 'jane.doe@example.com',
                'roles' => ['developer'],
                'groups' => ['admins'],
            ])
            ->assertOk()
            ->assertJsonStructure(['data', 'saved']);

        $user->refresh();

        $this->assertEquals($user->name, 'Jane Doe');

        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => 'developer',
        ]);

        $this->assertDatabaseHas('group_user', [
            'user_id' => $user->id,
            'group_id' => 'admins',
        ]);
    }

    #[Test]
    public function can_update_resource_with_required_if_published_validation()
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

        $post = Post::factory()->create(['published' => false]);
        $user = User::make()->makeSuper()->save();

        // Publishing without the required field should fail
        $this
            ->actingAs($user)
            ->patch(cp_route('runway.update', ['resource' => 'post', 'model' => $post->id]), [
                'published' => true,
                'title' => $post->title,
                'slug' => $post->slug,
                'body' => $post->body,
                'author_id' => [$post->author_id],
                'membership_status' => null,
            ])
            ->assertSessionHasErrors('membership_status');
    }
}
