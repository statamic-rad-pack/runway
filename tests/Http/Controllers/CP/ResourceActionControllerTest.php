<?php

namespace StatamicRadPack\Runway\Tests\Http\Controllers\CP;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\Action;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Tests\TestCase;

class ResourceActionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FooAction::register();
    }

    #[Test]
    public function can_run_action()
    {
        $this->assertFalse(FooAction::$hasRun);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post('/cp/runway/post/actions', [
                'action' => 'bar',
                'selections' => ['post'],
                'values' => [],
            ])
            ->assertOk()
            ->assertJson(['message' => 'Bar action run!']);

        $this->assertTrue(FooAction::$hasRun);
    }
}

class BarAction extends Action
{
    protected static $handle = 'bar';

    public static bool $hasRun = false;

    public function run($items, $values)
    {
        static::$hasRun = true;

        return 'Bar action run!';
    }
}
