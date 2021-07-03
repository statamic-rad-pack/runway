<?php

namespace DoubleThreeDigital\Runway\Tests\Http\Controllers;

use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->markTestIncomplete();

        $user = User::make()->makeSuper()->save();

        $posts = $this->postFactory(2);

        $this->actingAs($user)
            ->get(cp_route('runway.listing-api', ['resourceHandle' => 'post']))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'title' => $posts[0]->title,
                        'edit_url' => 'http://localhost/cp/runway/post/'.$posts[0]->id,
                        'delete_url' => 'http://localhost/cp/runway/post/'.$posts[0]->id,
                        '_id' => $posts[0]->id,
                    ],
                    [
                        'title' => $posts[1]->title,
                        'edit_url' => 'http://localhost/cp/runway/post/'.$posts[1]->id,
                        'delete_url' => 'http://localhost/cp/runway/post/'.$posts[1]->id,
                        '_id' => $posts[1]->id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_order_listing_rows()
    {
        $this->markTestIncomplete();
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
                        'edit_url' => 'http://localhost/cp/runway/post/'.$posts[0]->id,
                        'delete_url' => 'http://localhost/cp/runway/post/'.$posts[0]->id,
                        '_id' => $posts[0]->id,
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
