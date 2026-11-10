<?php

namespace StatamicRadPack\Runway\Tests\Feature\Models;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Config;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Runway;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\TestCase;

class CreateModelTest extends TestCase
{
    #[Test]
    public function can_create_resource()
    {
        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.create', ['resource' => 'post']))
            ->assertOk();
    }

    #[Test]
    public function cant_create_resource_if_resource_is_read_only()
    {
        Config::set('runway.resources.'.Post::class.'.read_only', true);

        Runway::discoverResources();

        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.create', ['resource' => 'post']))
            ->assertRedirect();
    }

    #[Test]
    public function cant_create_resource_when_blueprint_is_hidden()
    {
        $blueprint = Blueprint::find('runway::post');

        Blueprint::shouldReceive('find')->with('user')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::author')->andReturn(new \Statamic\Fields\Blueprint);
        Blueprint::shouldReceive('find')->with('runway::post')->andReturn($blueprint->setHidden(true));

        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('runway.create', ['resource' => 'post']))
            ->assertRedirect();
    }
}
