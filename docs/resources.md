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

class Order extends Model
{
    use HasRunwayResource; // [tl! add]
}
```

Finally, you need to create a blueprint for your resource, there's [documentation on that below](#resource-blueprints). 🔽

## Resource Blueprints

Blueprints are a key component to the content modeling process. They let you define the fields that should be available in the Control Panel and the way your data is stored.

### Creating a resource blueprint

Unfortunately, it's not yet possible to manage Runway blueprints in the Control Panel as there's no way for addons to "register" their own blueprints.

In the meantime, you can create a blueprint for a collection, then move the outputted YAML file to the `resources/blueprints` directory.

:::note Note!
Remember that the field handles in your blueprint should match up exactly with the column names in the database, otherwise bad things will happen.
:::

Now, to use the blueprint you just created, simply specify it's "namespace" (usually just its filename, minus the `.yaml` extension) as a `blueprint` key in your resources's config array:

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'blueprint' => 'order',
	],
],
```

If you want to store your resource's blueprint inside a directory, like `resources/blueprints/runway`, you'll need to specify the blueprint as `runway.blueprint_name`.

### More information

For more information about using Blueprints in Runway, please review the [Blueprints](/blueprints) page.

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

You may also specify if you want a resource to be 'read only' - eg. users will not be able to create records and when editing, all fields will be marked as read only and no save button will be displayed.

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

When Runway displays models inside the Control Panel (eg. in relationship fields, in search), it'll default to showing the first listable field it can find, based on your blueprint.

If you'd like to specify a different field, you may do so by setting the `title_field` option on your resource.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'title_field' => 'name',
	],
],
```

### Statamic Widgets

Runway can also display [Statamic widgets](https://statamic.dev/reference/widgets) on your resources index page, you are free to use first party widget comes with statamic and can also [create your own widget](https://statamic.dev/extending/widgets) to show for example stats for orders.

You can add configuration for `widgets` element on your resource.

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
	    
	    // define all widgets for order resource
		'widgets' => [
                [
                    'type' => 'earning',
                    'width' => 50
                ],
                [
                    'type' => 'collection',
                    'collection' => 'pages',
                    'width' => 50
                ],
            ],
        ],
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
