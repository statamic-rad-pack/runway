<?php

namespace StatamicRadPack\Runway\Tests\Search;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Search\Searchable;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class SearchablePolicyTest extends TestCase
{
    #[Test]
    public function user_with_view_permission_can_view_searchable()
    {
        $post = Post::factory()->create();
        $searchable = new Searchable($post);

        Role::make('test')->addPermission('view post')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertTrue($user->can('view', $searchable));
    }

    #[Test]
    public function user_without_view_permission_cannot_view_searchable()
    {
        $post = Post::factory()->create();
        $searchable = new Searchable($post);

        Role::make('test')->save();
        $user = User::make()->assignRole('test')->save();

        $this->assertFalse($user->can('view', $searchable));
    }

    #[Test]
    public function super_user_can_view_searchable()
    {
        $post = Post::factory()->create();
        $searchable = new Searchable($post);

        $user = User::make()->makeSuper()->save();

        $this->assertTrue($user->can('view', $searchable));
    }
}
