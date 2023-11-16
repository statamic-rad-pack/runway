<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\Post;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\Config;
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
    public function cant_create_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->get(cp_route('runway.create', ['resourceHandle' => 'post']))
            ->assertRedirect();
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
    public function cant_store_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['resourceHandle' => 'post']), [
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

    /**
     * @test
     * https://github.com/duncanmcclean/runway/pull/223
     */
    public function can_store_resource_and_ensure_computed_field_isnt_saved_to_database()
    {
        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['resourceHandle' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
                'age' => 25, // This is the computed field
            ])
            ->assertOk()
            ->assertJsonStructure([
                'redirect',
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/issues/331
     */
    public function can_store_resource_and_ensure_field_isnt_saved_to_database()
    {
        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['resourceHandle' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
                'dont_save' => 25,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'redirect',
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Jingle Bells',
        ]);
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/pull/247
     */
    public function can_store_resource_and_ensure_appended_attribute_doesnt_attempt_to_get_saved()
    {
        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['resourceHandle' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'excerpt' => 'This is an excerpt.',
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

    /**
     * @test
     * https://github.com/duncanmcclean/runway/pull/302
     */
    public function can_store_resource_with_nested_field()
    {
        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['resourceHandle' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'values->alt_title' => 'Batman Smells',
                'author_id' => [$author->id],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'redirect',
            ]);

        $this->assertDatabaseHas('posts', [
            'values->alt_title' => 'Batman Smells',
        ]);
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/issues/321
     */
    public function can_store_resource_and_ensure_date_comparison_validation_works()
    {
        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory();

        $this->actingAs($user)
            ->post(cp_route('runway.store', ['resourceHandle' => 'post']), [
                'title' => 'Jingle Bells',
                'slug' => 'jingle-bells',
                'body' => 'Jingle Bells, Jingle Bells, jingle all the way...',
                'author_id' => [$author->id],
                'start_date' => [
                    'date' => '2023-09-01',
                    'time' => '00:00',
                ],
                'end_date' => [
                    'date' => '2023-08-01',
                    'time' => '00:00',
                ],
            ])
            ->assertSessionHasErrors('start_date');

        $this->assertDatabaseMissing('posts', [
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
    public function cant_edit_resource_when_it_does_not_exist()
    {
        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->get(cp_route('runway.edit', ['resourceHandle' => 'post', 'record' => 12345]))
            ->assertNotFound()
            ->assertSee('Page Not Found');
    }

    /** @test */
    public function can_edit_resource_with_simple_date_field()
    {
        $fields = Config::get('runway.resources.'.Post::class.'.blueprint.sections.main.fields');

        $fields[] = [
            'handle' => 'created_at',
            'field' => [
                'type' => 'date',
                'mode' => 'single',
                'time_enabled' => false,
                'time_required' => false,
            ],
        ];

        Config::set('runway.resources.'.Post::class.'.blueprint.sections.main.fields', $fields);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();
        $post = $this->postFactory();

        $resource = Runway::findResource('post');
        $record = $resource->model()->where($resource->routeKey(), $post->getKey())->first();

        $this->assertEquals($post->getKey(), $record->getKey());

        $response = $this->actingAs($user)
            ->get(cp_route('runway.edit', [
                'resourceHandle' => 'post',
                'record' => $post->id,
            ]))
            ->assertOk();

        $this->assertEquals(
            [
                'date' => $post->created_at->format('Y-m-d'),
                'time' => null,
            ],
            $response->viewData('values')->get('created_at')
        );
    }

    /** @test */
    public function can_edit_resource_with_date_field_with_default_format()
    {
        $fields = Config::get('runway.resources.'.Post::class.'.blueprint.sections.main.fields');

        $fields[] = [
            'handle' => 'created_at',
            'field' => [
                'type' => 'date',
                'mode' => 'single',
                'format' => 'Y-m-d',
                'time_enabled' => false,
                'time_required' => false,
            ],
        ];

        Config::set('runway.resources.'.Post::class.'.blueprint.sections.main.fields', $fields);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();
        $post = $this->postFactory();

        $resource = Runway::findResource('post');
        $record = $resource->model()->where($resource->routeKey(), $post->getKey())->first();

        $this->assertEquals($post->getKey(), $record->getKey());

        $response = $this->actingAs($user)
            ->get(cp_route('runway.edit', [
                'resourceHandle' => 'post',
                'record' => $post->id,
            ]))
            ->assertOk();

        $this->assertEquals(
            [
                'date' => $post->created_at->format('Y-m-d'),
                'time' => null,
            ],
            $response->viewData('values')->get('created_at')
        );
    }

    /** @test */
    public function can_edit_resource_with_date_field_with_custom_format()
    {
        $fields = Config::get('runway.resources.'.Post::class.'.blueprint.sections.main.fields');

        $fields[] = [
            'handle' => 'created_at',
            'field' => [
                'type' => 'date',
                'mode' => 'single',
                'format' => 'Y-m-d H:i',
                'time_enabled' => true,
                'time_required' => false,
            ],
        ];

        Config::set('runway.resources.'.Post::class.'.blueprint.sections.main.fields', $fields);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();
        $post = $this->postFactory();

        $resource = Runway::findResource('post');
        $record = $resource->model()->where($resource->routeKey(), $post->getKey())->first();

        $this->assertEquals($post->getKey(), $record->getKey());

        $response = $this->actingAs($user)
            ->get(cp_route('runway.edit', [
                'resourceHandle' => 'post',
                'record' => $post->id,
            ]))
            ->assertOk();

        $this->assertEquals(
            [
                'date' => $post->created_at->format('Y-m-d'),
                'time' => $post->created_at->format('H:i'),
            ],
            $response->viewData('values')->get('created_at')
        );
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/pull/302
     */
    public function can_edit_resource_with_nested_field()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory(
            attributes: [
                'values' => [
                    'alt_title' => $this->faker->words(6, asText: true),
                ],
            ],
        );

        $this->actingAs($user)
            ->get(cp_route('runway.edit', ['resourceHandle' => 'post', 'record' => $post->id]))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->body)
            ->assertSee($post->values['alt_title']);
    }

    /** @test */
    public function can_edit_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

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
     * https://github.com/duncanmcclean/runway/pull/370
     */
    public function can_edit_resource_with_nested_field_cast_to_object_in_model()
    {
        $fields = Config::get('runway.resources.'.Post::class.'.blueprint.sections.main.fields');

        $fields[] = [
            'handle' => 'external_links->links',
            'field' => [
                'type' => 'grid',
                'fields' => [
                    [
                        'handle' => 'label',
                        'field' => ['type' => 'text'],
                    ],
                    [
                        'handle' => 'url',
                        'field' => ['type' => 'text'],
                    ],
                ],
            ],
        ];

        Config::set('runway.resources.'.Post::class.'.blueprint.sections.main.fields', $fields);
        Runway::discoverResources();

        $post = $this->postFactory(
            attributes: [
                'external_links' => [
                    'links' => [
                        [
                            'label' => 'NORAD Santa Tracker',
                            'url' => 'noradsanta.org',
                        ],
                        [
                            'label' => 'North Pole HQ',
                            'url' => 'northpole.com',
                        ],
                    ],
                ],
            ],
        );

        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->get(cp_route('runway.edit', ['resourceHandle' => 'post', 'record' => $post->id]))
            ->assertOk()
            ->assertSee($post->external_links->links[0]->label)
            ->assertSee($post->external_links->links[1]->url);
    }

    /** @test */
    public function can_update_resource()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->patch(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ]);

        $post->refresh();

        $this->assertSame($post->title, 'Santa is coming home');
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/issues/187
     */
    public function can_update_resource_when_being_updated_from_inline_publish_form()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->patch(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
                'from_inline_publish_form' => true,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ]);

        $post->refresh();

        $this->assertSame($post->title, 'Santa is coming home');
    }

    /** @test */
    public function cant_update_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->patch(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
            ])
            ->assertRedirect();

        $post->refresh();

        $this->assertNotSame($post->title, 'Santa is coming home');
    }

    /** @test */
    public function can_update_resource_and_ensure_computed_field_isnt_saved_to_database()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->patch(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
                'age' => 19, // This is the computed field
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ]);

        $post->refresh();

        $this->assertSame($post->title, 'Santa is coming home');
    }

    /**
     *  @test
     * https://github.com/duncanmcclean/runway/issues/331
     */
    public function can_update_resource_and_ensure__field_isnt_saved_to_database()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->patch(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'author_id' => [$post->author_id],
                'dont_save' => 19,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ]);

        $post->refresh();

        $this->assertSame($post->title, 'Santa is coming home');
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/pull/247
     */
    public function can_update_resource_and_ensure_appended_attribute_doesnt_attempt_to_get_saved()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->patch(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'excerpt' => 'This is an excerpt.',
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ]);

        $post->refresh();

        $this->assertSame($post->title, 'Santa is coming home');
    }

    /**
     * @test
     * https://github.com/duncanmcclean/runway/pull/302
     */
    public function can_update_resource_with_nested_field()
    {
        $user = User::make()->makeSuper()->save();

        $post = $this->postFactory();

        $this->actingAs($user)
            ->patch(cp_route('runway.update', ['resourceHandle' => 'post', 'record' => $post->id]), [
                'title' => 'Santa is coming home',
                'slug' => 'santa-is-coming-home',
                'body' => $post->body,
                'values->alt_title' => 'Claus is venturing out',
                'author_id' => [$post->author_id],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ]);

        $post->refresh();

        $this->assertSame($post->values['alt_title'], 'Claus is venturing out');
    }
}
