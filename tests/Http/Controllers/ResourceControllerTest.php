<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\Post;
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
                'listing-config',
                'columns',
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
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
            ])
            ->assertOk()
            ->assertJsonStructure([
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

    /**
     * @test
     * @dataProvider dateTimeProvider
     */
    public function can_edit_resource_with_datetime_field(array $options)
    {
        // Override the config to add the created_at field
        $configKey = 'runway.resources.' . Post::class . '.blueprint.sections.main.fields';
        $fields = $this->app['config']->get($configKey, []);

        $fields[] = [
            'handle' => 'created_at',
            'field'  => array_filter($options),
        ];

        $this->app['config']->set($configKey, $fields);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();
        $post = $this->postFactory();

        /** @var Resource $resource */
        $resource = Runway::findResource('post');
        $record = $resource->model()->where($resource->routeKey(), $post->getKey())->first();

        $this->assertEquals($post->getKey(), $record->getKey());

        $response = $this->actingAs($user)
            ->get(cp_route('runway.edit', [
                'resourceHandle' => 'post',
                'record'         => $post->id,
            ]))
            ->assertOk();

        $this->assertEquals(
            $post->created_at->format($options['expected_format'] ?? $options['format'] ??  'Y-m-d'),
            $response->viewData('values')->get('created_at')
        );
    }

    /** @test */
    public function can_update_resource()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'record',
                'resource_handle',
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
            ->assertOK();

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function dateTimeProvider()
    {
        return [
            'simple_date' => [[
                'type' => 'date',
                'format' => null,
                'time_enabled' => false,
                'time_required' => false,
            ]],
            'simple_date_with_default_format' => [[
                'type' => 'date',
                'format' => 'Y-m-d',
                'time_enabled' => false,
                'time_required' => false,
            ]],
            'datetime_with_format' => [[
                'type' => 'date',
                'format' => 'Y-m-d H:i:s',
                'expected_format' => 'Y-m-d H:i',
                'time_enabled' => true,
                'time_required' => false,
            ]]
        ];
    }
}
