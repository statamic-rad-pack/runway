<?php

namespace StatamicRadPack\Runway\Tests;

use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Statamic\Fields\Blueprint;

class RunwayTest extends TestCase
{
    /** @test */
    public function can_discover_and_get_all_resources()
    {
        Runway::discoverResources();

        $all = Runway::allResources();

        $this->assertTrue($all instanceof Collection);
        $this->assertCount(2, $all);

        $this->assertTrue($all->first() instanceof Resource);
        $this->assertEquals('post', $all->first()->handle());
        $this->assertTrue($all->first()->model() instanceof Model);
        $this->assertTrue($all->first()->blueprint() instanceof Blueprint);

        $this->assertTrue($all->last() instanceof Resource);
        $this->assertEquals('author', $all->last()->handle());
        $this->assertTrue($all->last()->model() instanceof Model);
        $this->assertTrue($all->last()->blueprint() instanceof Blueprint);
    }

    /** @test */
    public function can_find_resource()
    {
        Runway::discoverResources();

        $find = Runway::findResource('author');

        $this->assertTrue($find instanceof Resource);
        $this->assertEquals('author', $find->handle());
        $this->assertTrue($find->model() instanceof Model);
        $this->assertTrue($find->blueprint() instanceof Blueprint);
    }
}
