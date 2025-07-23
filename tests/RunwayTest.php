<?php

namespace StatamicRadPack\Runway\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Blueprint;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class RunwayTest extends TestCase
{
    #[Test]
    public function can_discover_and_get_all_resources()
    {
        Runway::discoverResources();

        $all = Runway::allResources()->values();

        $this->assertTrue($all instanceof Collection);
        $this->assertCount(3, $all);

        $this->assertTrue($all[0] instanceof Resource);
        $this->assertEquals('post', $all[0]->handle());
        $this->assertTrue($all[0]->model() instanceof Model);
        $this->assertTrue($all[0]->blueprint() instanceof Blueprint);

        $this->assertTrue($all[1] instanceof Resource);
        $this->assertEquals('author', $all[1]->handle());
        $this->assertTrue($all[1]->model() instanceof Model);
        $this->assertTrue($all[1]->blueprint() instanceof Blueprint);

        $this->assertTrue($all[2] instanceof Resource);
        $this->assertEquals('user', $all[2]->handle());
        $this->assertTrue($all[2]->model() instanceof Model);
        $this->assertTrue($all[2]->blueprint() instanceof Blueprint);
    }

    #[Test]
    public function can_find_resource()
    {
        Runway::discoverResources();

        $find = Runway::findResource('author');

        $this->assertTrue($find instanceof Resource);
        $this->assertEquals('author', $find->handle());
        $this->assertTrue($find->model() instanceof Model);
        $this->assertTrue($find->blueprint() instanceof Blueprint);
    }

    #[Test]
    public function can_check_if_resource_exists()
    {
        Runway::discoverResources();

        $this->assertTrue(Runway::hasResource('post'));
        $this->assertTrue(Runway::hasResource('author'));

        $this->assertFalse(Runway::hasResource('foo'));
    }
}
