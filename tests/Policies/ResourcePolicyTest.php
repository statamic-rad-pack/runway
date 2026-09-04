<?php

namespace StatamicRadPack\Runway\Tests\Policies;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ResourcePolicyTest extends TestCase
{
    #[Test]
    public function can_view_resource()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('view post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('view', $resource));
    }

    #[Test]
    public function can_view_resource_with_model()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('view post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('view', [$resource, new Post]));
    }

    #[Test]
    public function can_view_resource_with_only_edit_permission()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('edit post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('view', $resource));
    }

    #[Test]
    public function cant_view_resource_without_permission()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertFalse($user->can('view', $resource));
    }

    #[Test]
    public function can_create_resource()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('create post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('create', $resource));
    }

    public function can_edit_resource()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('edit post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('edit', $resource));
    }

    #[Test]
    public function can_edit_resource_with_model()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('edit post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('edit', [$resource, new Post]));
    }

    public function can_delete_resource()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('delete post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('delete', $resource));
    }

    #[Test]
    public function can_delete_resource_with_model()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('delete post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('delete', [$resource, new Post]));
    }

    #[Test]
    public function can_reorder_resource()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.orderable', true);

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('reorder post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('reorder', $resource));
    }

    #[Test]
    public function cant_reorder_resource_without_permission()
    {
        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.orderable', true);

        Runway::discoverResources();

        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('edit post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertFalse($user->can('reorder', $resource));
    }

    #[Test]
    public function cant_reorder_resource_when_resource_isnt_orderable()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('reorder post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertFalse($user->can('reorder', $resource));
    }
}
