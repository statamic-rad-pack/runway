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

To define a resource, add an item to the `resources` array like so:

```php
'resources' => [
	\App\Models\Order::class => [
		'name' => 'Orders',
	],
],
```

The key of the array item is the Eloquent model, and the array value contains the configuration for the resource.

The next thing you’ll want to do is create a blueprint for your model.

## Resource Blueprints

### Creating a new blueprint

Unfortunately, you can’t yet create/edit blueprints used for Runway in the Control Panel but it’s something we’re hoping comes in the future (it’s a Statamic Core change).

However, we can workaround it for now. Instead, create a new blueprint for one of your collections, taxonomies etc (it doesn’t matter).

When creating it, add in all the fields you need. Remember that the field handles need to match up with the column names, otherwise bad things will happen.

Once created via the CP, you’ll find the blueprint’s YAML file in a folder somewhere, probably something like `resources/blueprints/collections/pages/my-special-blueprint.yaml`.

You’ll want to move the file of the blueprint you just created into the root `resources/blueprints` folder.

Next, you’ll want to actually use the blueprint… (explained in the next point)

### Configure which blueprint to use

To use the blueprint you just created (or use one you’ve already created), you can simply specify it’s ‘namespace’ as a `blueprint` key in your resource configuration.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'blueprint' => 'order',
	],
],
```

If you’re blueprint is in the root `resources/blueprints` directory (which I’d recommend by the way), you just need to specify the blueprint’s handle.

If it’s inside a folder like `resources/blueprints/foo/order.yaml`, you can specify it like so: `foo.order`

### Generating migrations from your blueprints

If you’ve already went and created a blueprint for your model(s) and still to do the database side of things, Runway can help!
Runway can automatically generate migrations for your models, based on the fields defined in your blueprint, and their configurations.

To generate a migration for a specific blueprint:

```
php please runway:generate-migrations resource-handle
```

You may also run this same command for all resources pending a migration.

```
php please runway:generate-migrations
```

### Generating blueprints from your database

If you've already got an Eloquent model setup, Runway can help you turn it into a blueprint!

Before you can generate, you'll need to install the [`doctrine/dbal`](https://github.com/doctrine/dbal) package as it'll be used by Runway to analyse your database columns. You'll also need to have migrated your database already.

As well as having your model setup, you will also need to add the resource(s) to your `config/runway.php` config.

To generate a blueprint for a specific resource:

```
php please runway:generate-blueprints resource-handle
```

You may also run this same command for all resources:

```
php please runway:generate-blueprints
```

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

### CP Nav Icon

When Runway registers the CP Nav Item, it uses a rather generic icon. If you’d like to customise this, set `cp_icon` to the name of the icon you’d like to use instead.

Alternatively, if the icon you want isn’t [included in Statamic](https://github.com/statamic/cms/tree/3.1/resources/svg), you can also pass an SVG inline.

```php
'resources' => [
	\App\Models\Order::class => [
		'name' => 'Orders',
    	'listing' => [
      		'cp_icon' => 'date',
    	],
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
