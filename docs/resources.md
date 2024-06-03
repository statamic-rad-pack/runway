---
title: Resources
---

## What are resources?

For each of the Eloquent models you wish to use with Runway, you’ll need to define a ‘resource’.

A resource basically tells Runway about the model and how you’d like it to be configured - which blueprint to use, whether or not it should be manageable in the CP, etc.

## Defining resources

You can define resources inside of the configuration file published during installation. It’s located in `config/runway.php`.

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Configure the resources (models) you'd like to be available in Runway.
    |
    */

    'resources' => [
        //
    ],

];
```

There three steps to defining a Runway Resource: first you need to add it to Runway's `resources` array like so:

```php
'resources' => [
	\App\Models\Order::class => [
		'name' => 'Orders',
	],
],
```

Second, you need to add the `HasRunwayResource` trait to your Eloquent model.

```php
// app/Models/Order.php

use StatamicRadPack\Runway\Traits\HasRunwayResource; // [tl! add]

class Order extends Model
{
    use HasRunwayResource; // [tl! add]
}
```

Finally, you can start adding fields to your resource's blueprint. To learn more about using Blueprints in Runway, please review the [Blueprints](/blueprints) page.

## Configuring resources

There’s about a dozen configuration options available for resources, they are all documented below.

### Hidden

By default, Runway provides a Control Panel interface for managing your models.

If you’d like to hide the CP Nav Item that’s registered for this model, just say so:

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'hidden' => true,
	],
],
```

> Bear in mind, this will just hide the Nav Item for the CP interface, it won’t actually get rid of the routes being registered. If someone knows where to look, they could still use the CP to manage your models (they could guess the URL).

### Control Panel Icon

You should set `icon` to the name of the icon you’d like to use instead.

Alternatively, if the icon you want isn’t [included in Statamic](https://github.com/statamic/cms/tree/3.1/resources/svg), you can also pass an inline SVG.

```php
'resources' => [
	\App\Models\Order::class => [
		'name' => 'Orders',
        'cp_icon' => 'date',
	],
],
```

### Route

If you want to take advantage of Runway’s front-end routing abilities, you can pass in a `route` to enable it.

Your `route` can include Antlers code - the variables available are driven by the resource’s blueprint.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'route' => '/my-orders/{{ id }}',
	],
],
```

### Templates & Layouts

You may also specify the `template` and `layout` you want to use when front-end routing.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'route' => '/my-orders/{{ id }}',
		'template' => 'orders.show',
		'layout' => 'default',
	],
],
```

### Read Only

You may also specify if you want a resource to be 'read only' - eg. users will not be able to create models and when editing, all fields will be marked as read only and no save button will be displayed.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
        'read_only' => true,
	],
],
```

### Ordering

Sometimes you may want to change the order that your models are returned in the Control Panel listing table. You can use the `order_by` and `order_by_direction` configuration options to tell Runway the order you wish models to be returned.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',

        // In this case, orders will the highest total will be displayed first.
        'order_by' => 'total',
        'order_by_direction' => 'DESC',
	],
],
```

### Title field

When Runway displays models inside inside the Control Panel (eg. in relationship fields, in search), it'll default to showing the first listable field it can find, based on your blueprint.

If you'd like to specify a different field, you may do so by setting the `title_field` option on your resource.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'title_field' => 'name',
	],
],
```

### Eager Loading

To help with performance, Runway will automatically ["eager load"](https://laravel.com/docs/master/eloquent-relationships#eager-loading) any Eloquent relationships it knows about based on the fields you've defined in your blueprint.

However, if you wish, you can override the relationships that get eager loaded by providing the `with` option on your resource:

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'with' => ['lineItems', 'customer'],
	],
],
```

### Publish States

If you're storing content you'd like to be able to store without publishing right away, you can use the `published` config option to add a "Published" toggle to your Runway model:

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'published' => true, // Assumes the model has a `published` boolean column.
		'published' => 'active', // Otherwise, you can specify the column name.
	],
],
```

In addition to adding the "Published" toggle, you'll see status indicators in the Control Panel and unpublished models will be filtered out during augmentation, in a very similar way to how Statamic deals with unpublished content.

## Actions

In much the same way with entries, you can create custom Actions which will be usable in the listing tables provided by Runway.

You can register them in the same way as you normally would.

The only thing that’s different is the fact that instead of filtering down to just `Entry` objects for example, you can filter by your model, like `Order`.

```php
use App\Models\Order;

public function visibleTo($item)
{
    return $item instanceof Order;
}
```

## List of Resources

If you're unsure about the handle of a resource, you may want to check it. You may do so with the `php please runway:resources` command which will display a list of Runway Resources.

![Resource List command](/img/runway/resource-list.png)
