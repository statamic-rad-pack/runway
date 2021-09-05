---
title: 'Control Panel'
---

One of Runway’s core features is the Control Panel integration. Runway will create listing and publish form pages for each of your resources.

![Screenshot of Control Panel Listing View](/assets/cp-listing-view.png)

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


## Custom Icon for CP Nav Item
Runway has a rather generic icon for resources in the Control Panel Nav. Feel free to change this to something else that better suits your use case (in fact, I’d encourage it). 

You can either provide the name of an existing icon [packaged into Statamic Core](https://github.com/statamic/cms/tree/3.1/resources/svg) or inline the SVG as a string.

```php
// config/runway.php

'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'listing' => [
			'cp_icon' => 'date',
		],
	],
],
```


## Permissions
![Screenshot of Runway's User Permissions](/assets/cp-user-permissions.png)

If you have other users who are not ‘super users’, you may wish to also give them permission to view_create_update specific resources.

Runway gives you granular control over which actions users can/cannot do for each of your resources.

## Actions
Runway supports using [Statamic Actions](https://statamic.dev/extending/actions#content) to preform tasks on your models.

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
