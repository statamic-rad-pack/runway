<?php

namespace StatamicRadPack\Runway\UpdateScripts;

use Statamic\Facades\Role;
use Statamic\UpdateScripts\UpdateScript;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class AddManagePublishStatesPermissionToRoles extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('7.5.4');
    }

    public function update()
    {
        Role::all()->each(function ($role) {
            $requiresSave = false;

            Runway::allResources()->each(function (Resource $resource) use ($role, &$requiresSave) {
                if ($resource->hasPublishStates() && $role->hasPermission("create {$resource->handle()}")) {
                    $role->addPermission("publish {$resource->handle()}");
                    $requiresSave = true;
                }

            });

            if ($requiresSave) {
                $role->save();
            }
        });
    }
}
