<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\User;

class ResourceListingControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_with_no_permissions_cannot_access_resource_listing()
    {
        $user = User::make()->save();

        $this->actingAs($user)
            ->get(cp_route('runway.listing-api', ['resourceHandle' => 'post']))
            ->assertRedirect();
    }

    /** @test */
    public function can_sort_listing_rows()
    {
        $user = User::make()->makeSuper()->save();

        $posts = $this->postFactory(2);

        $this->actingAs($user)
            ->get(cp_route('runway.listing-api', ['resourceHandle' => 'post']))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'title' => $posts[0]->title,
                        'edit_url' => 'http://localhost/cp/runway/post/' . $posts[0]->id,
                        'id' => $posts[0]->id,
                    ],
                    [
                        'title' => $posts[1]->title,
                        'edit_url' => 'http://localhost/cp/runway/post/' . $posts[1]->id,
                        'id' => $posts[1]->id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function listing_rows_are_ordered_as_per_config()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Post.order_by', 'id');
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Post.order_by_direction', 'desc');

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();

        $posts = $this->postFactory(2);

        $this->actingAs($user)
            ->get(cp_route('runway.listing-api', ['resourceHandle' => 'post']))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'title' => $posts[1]->title,
                        'edit_url' => 'http://localhost/cp/runway/post/' . $posts[1]->id,
                        'id' => $posts[1]->id,
                    ],
                    [
                        'title' => $posts[0]->title,
                        'edit_url' => 'http://localhost/cp/runway/post/' . $posts[0]->id,
                        'id' => $posts[0]->id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_search()
    {
        $user = User::make()->makeSuper()->save();

        $posts = $this->postFactory(2);

        $posts[0]->update(['title' => 'Apple Pie']);

        $this->actingAs($user)
            ->get(cp_route('runway.listing-api', ['resourceHandle' => 'post', 'search' => 'Apple']))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'title' => $posts[0]->title,
                        'edit_url' => 'http://localhost/cp/runway/post/' . $posts[0]->id,
                        'id' => $posts[0]->id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_search_records_with_has_many_relationship()
    {
        Config::set('runway.resources.DoubleThreeDigital\Runway\Tests\Author.blueprint.sections.main.fields', [
            [
                'handle' => 'name',
                'field' => [
                    'type' => 'text',
                ],
            ],
            [
                'handle' => 'posts',
                'field' => [
                    'type' => 'has_many',
                    'resource' => 'post',
                    'mode' => 'select',
                ],
            ],
        ]);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();

        $author = $this->authorFactory(1, ['name' => 'Colin The Caterpillar']);

        $posts = $this->postFactory(5, ['author_id' => $author->id]);

        $this->actingAs($user)
            ->get(cp_route('runway.listing-api', [
                'resourceHandle' => 'author',
                'search' => 'Colin',
                'columns' => 'name,posts',
            ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Colin The Caterpillar',
                        'edit_url' => 'http://localhost/cp/runway/author/' . $author->id,
                        'id' => $author->id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_paginate_results()
    {
        $user = User::make()->makeSuper()->save();

        $posts = $this->postFactory(15);

        $this->actingAs($user)
            ->get(cp_route('runway.listing-api', ['resourceHandle' => 'post']) . '?perPage=5')
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'per_page' => '5',
                    'to' => 5,
                    'total' => 15,
                ],
            ]);
    }
}
