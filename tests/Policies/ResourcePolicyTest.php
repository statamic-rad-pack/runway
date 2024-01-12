<?php

namespace StatamicRadPack\Runway\Tests\Policies;

use Statamic\Facades\Role;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ResourcePolicyTest extends TestCase
{
    /** @test */
    public function can_view_resource()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('view post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('view', $resource));
    }

    /** @test */
    public function can_view_resource_with_model()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('view post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('view', [$resource, new Post]));
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function can_delete_resource_with_model()
    {
        $resource = Runway::findResource('post');

        Role::make('test')->addPermission('delete post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('delete', [$resource, new Post]));
    }
}
