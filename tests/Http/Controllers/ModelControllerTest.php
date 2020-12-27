<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Tests\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Statamic\Facades\User;

class ModelControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function get_model_index()
    {
        $user = User::make()->makeSuper()->save();

        $postOne = $this->makeFactory();
        $postTwo = $this->makeFactory();

        $this->actingAs($user)
            ->get(cp_route('runway.index', ['model' => 'post']))
            ->assertOk()
            ->assertViewIs('runway::index')
            ->assertSee([
                $postOne->title,
                $postTwo->title,
            ]);
    }

    /** @test */
    public function can_create_model()
    {
        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->get(cp_route('runway.create', ['model' => 'post']))
            ->assertOk();
    }

    /** @test */
    public function can_store_model()
    {
        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['model' => 'post']), [
                'title' => 'Jingle Bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
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
    public function can_edit_model()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->makeFactory();

        $this->actingAs($user)
            ->get(cp_route('runway.edit', ['model' => 'post', 'record' => $post->id]))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->body);
    }

    /** @test */
    public function can_update_model()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->makeFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.update', ['model' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'body' => $post->body,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'record',
            ]);

        $post->refresh();

        $this->assertSame($post->title, 'Santa is coming home');
    }

    /** @test */
    public function can_destroy_model()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->makeFactory();

        $this->actingAs($user)
            ->delete(cp_route('runway.destroy', ['model' => 'post', 'record' => $post->id]))
            ->assertRedirect('/cp/runway/post')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    protected function makeFactory()
    {
        return Post::create([
            'title' => join(' ', $this->faker->words(6)),
            'body' => join(' ', $this->faker->paragraphs(10)),
        ]);
    }
}
