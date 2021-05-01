<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\User;

class ResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_model_index()
    {
        $user = User::make()->makeSuper()->save();

        $posts = $this->postFactory(2);

        $this->actingAs($user)
            ->get(cp_route('runway.index', ['resourceHandle' => 'post']))
            ->assertOk()
            ->assertViewIs('runway::index')
            ->assertSee([
                $posts[0]->title,
                $posts[1]->title,
            ]);
    }

    /** @test */
    public function can_create_resource()
    {
        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->get(cp_route('runway.create', ['resourceHandle' => 'post']))
            ->assertOk();
    }

    /** @test */
    public function can_store_resource()
    {
        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['resourceHandle' => 'post']), [
                'title' => 'Jingle Bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'record',
                'redirect',
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    /** @test */
    public function can_edit_resource()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->get(cp_route('runway.edit', ['resourceHandle' => 'post', 'record' => $post->id]))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->body);
    }

    /** @test */
    public function can_update_resource()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'record',
            ]);

        $post->refresh();

        $this->assertSame($post->title, 'Santa is coming home');
    }

    /** @test */
    public function can_destroy_resource()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->delete(cp_route('runway.destroy', ['resourceHandle' => 'post', 'record' => $post->id]))
            ->assertRedirect('/cp/runway/post')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }
}
