<?php

namespace StatamicRadPack\Runway\Tests\UpdateScripts;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use StatamicRadPack\Runway\Tests\TestCase;
use StatamicRadPack\Runway\UpdateScripts\AddManagePublishStatesPermission;

class AddManagePublishStatesPermissionTest extends TestCase
{
    use RunsUpdateScripts;

    #[Test]
    public function publish_permission_is_added_to_role_with_create_permission()
    {
        Role::make('test')
            ->addPermission('view post')
            ->addPermission('create post')
            ->save();

        $this->runUpdateScript(AddManagePublishStatesPermission::class);

        $this->assertEquals([
            'view post',
            'create post',
            'publish post',
        ], Role::find('test')->permissions()->all());
    }

    #[Test]
    public function publish_permission_is_not_added_to_role_without_create_permission()
    {
        Role::make('test')
            ->addPermission('view post')
            ->save();

        $this->runUpdateScript(AddManagePublishStatesPermission::class);

        $this->assertEquals([
            'view post',
        ], Role::find('test')->permissions()->all());
    }
}
