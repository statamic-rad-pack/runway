<?php

namespace StatamicRadPack\Runway\Tests\Http\Controllers\CP;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Structures\RunwayStructure;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class ReorderModelsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('runway.resources.StatamicRadPack\Runway\Tests\Fixtures\Models\Post.orderable', true);

        Runway::discoverResources();
    }

    #[Test]
    public function can_reorder_models()
    {
        $user = User::make()->makeSuper()->save();
        $posts = Post::factory()->count(3)->create();

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'post']), [
                'ids' => [$posts[2]->id, $posts[0]->id, $posts[1]->id],
                'page' => 1,
                'perPage' => 3,
            ])
            ->assertOk();

        $this->assertEquals(1, $this->orderOf($posts[2]));
        $this->assertEquals(2, $this->orderOf($posts[0]));
        $this->assertEquals(3, $this->orderOf($posts[1]));
    }

    #[Test]
    public function can_reorder_models_on_a_later_page()
    {
        $user = User::make()->makeSuper()->save();
        $posts = Post::factory()->count(4)->create();

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'post']), [
                'ids' => [$posts[3]->id, $posts[2]->id],
                'page' => 2,
                'perPage' => 2,
            ])
            ->assertOk();

        $this->assertEquals(1, $this->orderOf($posts[0]));
        $this->assertEquals(2, $this->orderOf($posts[1]));
        $this->assertEquals(3, $this->orderOf($posts[3]));
        $this->assertEquals(4, $this->orderOf($posts[2]));
    }

    #[Test]
    public function models_without_a_structure_are_given_one_when_reordering()
    {
        $user = User::make()->makeSuper()->save();
        $posts = Post::factory()->count(3)->create();

        RunwayStructure::query()->delete();

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'post']), [
                'ids' => [$posts[1]->id, $posts[0]->id, $posts[2]->id],
                'page' => 1,
                'perPage' => 3,
            ])
            ->assertOk();

        $this->assertEquals(1, $this->orderOf($posts[1]));
        $this->assertEquals(2, $this->orderOf($posts[0]));
        $this->assertEquals(3, $this->orderOf($posts[2]));
    }

    #[Test]
    public function models_hidden_from_the_listing_are_left_alone()
    {
        $user = User::make()->makeSuper()->save();
        $posts = Post::factory()->count(4)->create();

        Blink::put('RunwayListingScopeWhereIn', [$posts[0]->id, $posts[2]->id, $posts[3]->id]);

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'post']), [
                'ids' => [$posts[3]->id, $posts[0]->id, $posts[2]->id],
                'page' => 1,
                'perPage' => 3,
            ])
            ->assertOk();

        $this->assertEquals(1, $this->orderOf($posts[3]));
        $this->assertEquals(2, $this->orderOf($posts[0]));
        $this->assertEquals(3, $this->orderOf($posts[2]));
        $this->assertEquals(2, $this->orderOf($posts[1]));
    }

    #[Test]
    public function cant_reorder_models_when_listing_is_out_of_date()
    {
        $user = User::make()->makeSuper()->save();
        $posts = Post::factory()->count(3)->create();

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'post']), [
                'ids' => [$posts[1]->id, $posts[0]->id],
                'page' => 1,
                'perPage' => 3,
            ])
            ->assertStatus(409);

        $this->assertEquals(1, $this->orderOf($posts[0]));
        $this->assertEquals(2, $this->orderOf($posts[1]));
        $this->assertEquals(3, $this->orderOf($posts[2]));
    }

    #[Test]
    public function cant_reorder_models_without_ids_page_and_per_page()
    {
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'post']), [])
            ->assertJsonValidationErrors(['ids', 'page', 'perPage']);
    }

    #[Test]
    public function cant_reorder_models_when_resource_isnt_orderable()
    {
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'author']), [
                'ids' => [],
                'page' => 1,
                'perPage' => 3,
            ])
            ->assertNotFound();
    }

    #[Test]
    public function user_without_permission_cant_reorder_models()
    {
        Role::make('test')->addPermission('edit post')->save();
        $user = User::make()->assignRole('test')->save();
        $posts = Post::factory()->count(2)->create();

        $this
            ->actingAs($user)
            ->postJson(cp_route('runway.reorder', ['resource' => 'post']), [
                'ids' => [$posts[1]->id, $posts[0]->id],
                'page' => 1,
                'perPage' => 2,
            ])
            ->assertForbidden();
    }

    private function orderOf(Post $post): ?int
    {
        return $post->runwayStructure()->value('order');
    }
}
