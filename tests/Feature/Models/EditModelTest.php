<?php

namespace StatamicRadPack\Runway\Tests\Feature\Models;

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
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

class EditModelTest extends TestCase
{
    #[Test]
    public function can_edit_resource()
    {
        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->body);
    }

    #[Test]
    public function can_view_resource_in_read_only_mode_with_only_view_permission()
    {
        $post = Post::factory()->create();

        Role::make('test')->addPermission('access cp')->addPermission('view post')->save();
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('runway::Edit')
                ->where('readOnly', true)
            );
    }

    #[Test]
    public function resource_is_not_read_only_when_user_has_edit_permission()
    {
        $post = Post::factory()->create();

        Role::make('test')->addPermission('access cp')->addPermission('edit post')->save();
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('runway::Edit')
                ->where('readOnly', false)
            );
    }

    #[Test]
    public function cant_edit_resource_without_view_permission()
    {
        $post = Post::factory()->create();

        Role::make('test')->addPermission('access cp')->save();
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertRedirect();
    }

    #[Test]
    public function cant_edit_resource_when_it_does_not_exist()
    {
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => 12345]))
            ->assertNotFound();
    }

    #[Test]
    public function can_edit_resource_with_simple_date_field()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->ensureField('created_at', [
            'type' => 'date',
            'mode' => 'single',
            'time_enabled' => false,
            'time_required' => false,
        ]));
        Blueprint::shouldReceive('getAdditionalNamespaces')->andReturn(collect(['runway' => base_path('resources/blueprints/runway')]))->zeroOrMoreTimes();

        $user = User::make()->makeSuper()->save();
        $post = Post::factory()->create();

        $resource = Runway::findResource('post');
        $model = $resource->model()->where($resource->routeKey(), $post->getKey())->first();

        $this->assertEquals($post->getKey(), $model->getKey());

        $response = $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', [
                'resource' => 'post',
                'model' => $post->id,
            ]))
            ->assertOk();

        // Seconds aren't enabled, so we shouldn't have any.
        $this->assertEquals(
            $post->created_at->startOfMinute()->toIso8601ZuluString('millisecond'),
            $response->inertiaProps('values')['created_at']
        );
    }

    #[Test]
    public function can_edit_resource_with_date_field_with_default_format()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->ensureField('created_at', [
            'type' => 'date',
            'mode' => 'single',
            'format' => 'Y-m-d',
            'time_enabled' => false,
            'time_required' => false,
        ]));
        Blueprint::shouldReceive('getAdditionalNamespaces')->andReturn(collect(['runway' => base_path('resources/blueprints/runway')]))->zeroOrMoreTimes();

        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $resource = Runway::findResource('post');
        $model = $resource->model()->where($resource->routeKey(), $post->getKey())->first();

        $this->assertEquals($post->getKey(), $model->getKey());

        $response = $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', [
                'resource' => 'post',
                'model' => $post->id,
            ]))
            ->assertOk();

        // Time isn't enabled, so expect just the date.
        $this->assertEquals(
            $post->created_at->startOfDay()->format('Y-m-d'),
            $response->inertiaProps('values')['created_at']
        );
    }

    #[Test]
    public function can_edit_resource_with_date_field_with_custom_format()
    {
        $postBlueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($postBlueprint->ensureField('created_at', [
            'type' => 'date',
            'mode' => 'single',
            'format' => 'Y-m-d H:i',
            'time_enabled' => true,
            'time_required' => false,
        ]));
        Blueprint::shouldReceive('getAdditionalNamespaces')->andReturn(collect(['runway' => base_path('resources/blueprints/runway')]))->zeroOrMoreTimes();

        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $resource = Runway::findResource('post');
        $model = $resource->model()->where($resource->routeKey(), $post->getKey())->first();

        $this->assertEquals($post->getKey(), $model->getKey());

        $response = $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', [
                'resource' => 'post',
                'model' => $post->id,
            ]))
            ->assertOk();

        // Seconds aren't enabled, so we shouldn't have any.
        $this->assertEquals(
            $post->created_at->startOfMinute()->toIso8601ZuluString('millisecond'),
            $response->inertiaProps('values')['created_at']
        );
    }

    #[Test]
    public function can_edit_resource_with_nested_field()
    {
        $post = Post::factory()->create([
            'values' => [
                'alt_title' => 'Im Toby Ziegler, and I work at the White House.',
            ],
        ]);

        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->body)
            ->assertSee('Im Toby Ziegler, and I work at the White House.');
    }

    #[Test]
    public function can_edit_resource_when_title_field_is_a_nested_field()
    {
        Config::set('runway.resources.'.Post::class.'.title_field', 'values_alt_title');

        Runway::discoverResources();

        $post = Post::factory()->create(['values' => ['alt_title' => 'Alternative Title']]);
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('runway::Edit')
                ->where('title', 'Alternative Title')
            );
    }

    #[Test]
    public function can_edit_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

        $post = Post::factory()->create();
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->body);
    }

    #[Test]
    public function can_edit_resource_with_nested_field_cast_to_object_in_model()
    {
        $post = Post::factory()->create([
            'external_links' => [
                'links' => [
                    ['label' => 'NORAD Santa Tracker', 'url' => 'noradsanta.org'],
                    ['label' => 'North Pole HQ', 'url' => 'northpole.com'],
                ],
            ],
        ]);

        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->get(cp_route('runway.edit', ['resource' => 'post', 'model' => $post->id]))
            ->assertOk()
            ->assertSee($post->external_links->links[0]->label)
            ->assertSee($post->external_links->links[1]->url);
    }

    #[Test]
    public function can_edit_resource_if_model_is_user_model()
    {
        Config::set('auth.providers.users.model', UserModel::class);

        $user = UserModel::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        UserGroup::make('admins')->title('Admins')->save();
        DB::table('group_user')->insert(['user_id' => $user->id, 'group_id' => 'admins']);

        Role::make('developer')->title('Developer')->save();
        DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => 'developer']);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('runway.edit', ['resource' => 'user', 'model' => $user->id]))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee('developer')
            ->assertSee('admins');
    }
}
