<?php

namespace DoubleThreeDigital\Runway\UpdateScripts;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Facades\Yaml;
use Statamic\UpdateScripts\UpdateScript;

class ChangePermissionNames extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('4.0.0');
    }

    public function update()
    {
        if (config('statamic.users.repository') !== 'file') {
            $this->console()->warn("Runway has made updates to it's permissions. You'll need to update your user permissions manually.");
            $this->console()->warn('Please see the upgrade guide for more information: TODO');

            return;
        }

        $roles = Yaml::file(config('statamic.users.repositories.file.paths.roles'))->parse();

        $roles = collect($roles)
            ->map(function ($role) {
                $role['permissions'] = collect($role['permissions'] ?? [])
                    ->map(function ($permission) {
                        Runway::allResources()->each(function (Resource $resource) use (&$permission) {
                            $permission = Str::of($permission)
                                ->replace("View {$resource->plural()}", "view {$resource->handle()}")
                                ->replace("Edit {$resource->plural()}", "edit {$resource->handle()}")
                                ->replace("Create new {$resource->singular()}", "create {$resource->handle()}")
                                ->replace("Delete {$resource->plural()}", "delete {$resource->handle()}")
                                ->__toString();
                        });

                        return $permission;
                    })
                    ->values()
                    ->toArray();

                return $role;
            })
            ->toArray();

        $yaml = Yaml::dump($roles);

        File::put(config('statamic.users.repositories.file.paths.roles'), $yaml);
    }
}
