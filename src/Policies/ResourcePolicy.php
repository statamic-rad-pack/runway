<?php

namespace DoubleThreeDigital\Runway\Policies;

use DoubleThreeDigital\Runway\Resource;
use Statamic\Contracts\Auth\User;

class ResourcePolicy
{
    public function view(User $user, Resource $resource)
    {
        return $user->hasPermission("view {$resource->handle()}");
    }

    public function create(User $user, Resource $resource)
    {
        return $user->hasPermission("create {$resource->handle()}");
    }
    
    public function edit(User $user, Resource $resource)
    {
        return $user->hasPermission("edit {$resource->handle()}");
    }
    
    public function delete(User $user, Resource $resource)
    {
        return $user->hasPermission("delete {$resource->handle()}");
    }
}
