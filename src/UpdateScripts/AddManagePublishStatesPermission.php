<?php

namespace StatamicRadPack\Runway\UpdateScripts;

use Statamic\Facades\Role;
use Statamic\UpdateScripts\UpdateScript;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class AddManagePublishStatesPermission extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('7.6.0');
    }

    public function update()
    {
        Role::all()->each(function ($role) {
            Runway::allResources()
                ->filter->hasPublishStates()
                ->filter(fn (Resource $resource) => $role->hasPermission("create {$resource->handle()}"))
                ->each(fn (Resource $resource) => $role->addPermission("publish {$resource->handle()}"));

            $role->save();
        });
    }
}
