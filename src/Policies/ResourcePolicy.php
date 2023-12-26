<?php

namespace StatamicRadPack\Runway\Policies;

use StatamicRadPack\Runway\Resource;
use Statamic\Facades\User;

class ResourcePolicy
{
    public function view($user, Resource $resource, $model = null)
    {
        return User::fromUser($user)->hasPermission("view {$resource->handle()}");
    }

    public function create($user, Resource $resource)
    {
        return User::fromUser($user)->hasPermission("create {$resource->handle()}");
    }

    public function edit($user, Resource $resource, $model = null)
    {
        return User::fromUser($user)->hasPermission("edit {$resource->handle()}");
    }

    public function delete($user, Resource $resource, $model = null)
    {
        return User::fromUser($user)->hasPermission("delete {$resource->handle()}");
    }
}
