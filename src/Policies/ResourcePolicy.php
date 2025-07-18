<?php

namespace StatamicRadPack\Runway\Policies;

use Statamic\Facades\User;
use StatamicRadPack\Runway\Resource;

class ResourcePolicy
{
    public function before($user)
    {
        $user = User::fromUser($user);

        if ($user->isSuper()) {
            return true;
        }
    }

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

    public function publish($user, Resource $resource, $model = null)
    {
        return User::fromUser($user)->hasPermission("publish {$resource->handle()}");
    }
}
