<?php

namespace StatamicRadPack\Runway\Tests\UpdateScripts;

use Illuminate\Support\Facades\File;
use StatamicRadPack\Runway\Tests\TestCase;
use StatamicRadPack\Runway\UpdateScripts\ChangePermissionNames;

class ChangePermissionNamesTest extends TestCase
{
    use RunsUpdateScripts;

    /** @test */
    public function it_can_change_permission_names()
    {
        File::ensureDirectoryExists(resource_path('users'));

        File::put(
            config('statamic.users.repositories.file.paths.roles'),
            File::get(__DIR__.'/../__fixtures__/resources/users/roles.yaml')
        );

        $this->runUpdateScript(ChangePermissionNames::class);

        $roles = File::get(config('statamic.users.repositories.file.paths.roles'));

        $this->assertStringContainsString('view post', $roles);
        $this->assertStringNotContainsString('View Posts', $roles);

        $this->assertStringContainsString('edit post', $roles);
        $this->assertStringNotContainsString('Edit Posts', $roles);

        $this->assertStringContainsString('create post', $roles);
        $this->assertStringNotContainsString('Create new Post', $roles);

        $this->assertStringContainsString('delete post', $roles);
        $this->assertStringNotContainsString('Delete Post', $roles);
    }
}
