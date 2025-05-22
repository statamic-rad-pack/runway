---
title: 'Control Panel'
---

One of Runway’s core features is the Control Panel integration. Runway will create listing and publish form pages for each of your resources.

![Screenshot of Control Panel Listing View](/img/runway/cp-listing-view.png)

The Control Panel functionality is built to work in exactly the same way you’d expect with entries. You can set filters, define scopes and use custom actions.

## Disable Control Panel functionality

Technically, you can’t fully disable Runway’s CP feature. However, what you can do is hide the Nav Item from the Control Panel.

```php
// config/runway.php

'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
	    'hidden' => true,
	],
],
```

## Permissions

![Screenshot of Runway's User Permissions](/img/runway/cp-user-permissions.png)

If you have other users who are not 'super users', you may wish to also give them permission to view/create/update specific resources.

Runway gives you granular control over which actions users can/cannot do for each of your resources.

### Permission labels

You may customize permission labels by adding a `resources/lang/{lang}/runway.php` file.

```php
// resources/lang/pt_BR/runway.php

'permissions' => [
    'create' => 'Criar :resource',
    'delete' => 'Excluir :resource',
    'edit' => 'Editar :resource',
    'view' => 'Visualizar :resource'
]
```

In the example above, `:resource` will be replaced by the resource's `name`.

### Custom permissions

If you need to, you can add new permissions to the existing ones created by Runway:

```php
// app/Providers/AppServiceProvider.php

public function boot()
{
    Permission::group('Runway', function () {
        foreach (Runway::allResources() as $resource) {
            Permission::get("edit {$resource->handle()}")->addChild(
                Permission::make("edit other owners {$resource->handle()}")
                    ->label(trans('runway.permissions.edit_other_owners_resource', [
                        'resource' => $resource->name()
                    ]))
            );
        }
    });
}
```

## Authorization

If you need to, you can change how resource actions are authorized by extending and rebinding the `ResourcePolicy` class.

```php
// app/Policies/ResourcePolicy.php

<?php

namespace App\Policies;

use StatamicRadPack\Runway\Policies\ResourcePolicy as RunwayResourcePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy extends RunwayResourcePolicy
{
    use HandlesAuthorization;

    public function edit($user, $resource)
    {
        // Do custom checks here before running the default ones

        // If custom checks didn't return, run default checks
        return parent::edit($user, $resource);
    }
}
```

```php
// app/Providers/AppServiceProvider.php

<?php

namespace App\Providers;

use App\Policies\ResourcePolicy;
use StatamicRadPack\Runway\Policies\ResourcePolicy as RunwayResourcePolicy;

public function boot()
{
    $this->app->bind(RunwayResourcePolicy::class, ResourcePolicy::class);
}
```

## Actions

Runway supports using [Statamic Actions](https://statamic.dev/extending/actions#content) to perform tasks on your models.

You may register your own custom actions, as per the Statamic documentation. If you wish to only show an action on one of your models, you can filter it down in the `visibleTo` method.

```php
use App\Models\Post;

class YourCustomAction extends Action
{
	public function visibleTo($item)
	{
		return $item instanceof Post;
	}
}
```

## Filters

Runway provides a "Fields" filter to filter models in the Control Panel based on blueprint fields.

![Control Panel Filters](/img/runway/cp-filters.png)

You can create your own filters using [query scopes and filters](https://statamic.dev/extending/query-scopes-and-filters#filters). To make your filter visible in a Runway resource, specify it in `visibleTo` method, specifying the context of the resource and the key as 'runway'.

```php
/**
 * Determine when the filter is shown.
 *
 * @param string $key
 * @return bool
 */
public function visibleTo($key)
{
    return $key === 'runway' && $this->context['resource'] === 'product';
}
```

## Scoping Control Panel Results

If you don't want to return everything, you may add a scope (`runwayListing`) to limit the returned results.

```php
class YourModel extends Model
{
	public function scopeRunwayListing($query)
	{
		return $query->where('something', true);
	}
}
```

### Scoping search results

On top of scoping your Control Panel results, you may also scope how Runway searches your models. To do this, you may specify a `runwaySearch` scope.

```php
class YourModel extends Model
{
	public function scopeRunwaySearch($query, $searchString)
	{
		// Your own search logic
	}
}
```
