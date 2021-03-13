![Banner](https://raw.githubusercontent.com/doublethreedigital/runway/master/banner.png)

## Runway

Runway allows you to easilly manage your Eloquent models straight from your Statamic Control Panel. Runway also gives you the option of outputting your Eloquent models in your Antlers templates. No need for a custom tag, it's all built-in.

This repository contains the source code of Runway. While Runway is free and doesn't require a license, you can [donate to Duncan](https://duncanm.dev/donate), the developer behind Runway, to show your appreciation.

## Installation

1. Install via Composer `composer require doublethreedigital/runway`
2. Publish the configuration file `php artisan vendor:publish --tag="runway-config"`
3. Configure the blueprint for each of the Eloquent models you wish to use with Runway.

## Configuration

During installation, you'll publish a configuration file for Runway to `config/runway.php`. The contents of said file look like this:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Eloquent Models
    |--------------------------------------------------------------------------
    |
    | Configure the eloquent models you wish to be editable with Runway and
    | the fields you want on the model's blueprint.
    |
    */

    'models' => [
        // \App\Models\Order::class => [
        //     'name' => 'Orders',

        //     'blueprint' => [
        //         'sections' => [
        //             'main' => [
        //                 'fields' => [
        //                     [
        //                         'handle' => 'price',
        //                         'field' => [
        //                             'type' => 'number',
        //                             'validate' => 'required',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ],

        //     'listing' => [
        //         'columns' => [
        //             'order_number',
        //             'price',
        //         ],

        //         'sort' => [
        //             'column' => 'paid_at',
        //             'direction' => 'desc',
        //         ],
        //     ],
        // ],
    ],

];
```

To configure the models you'd like to use Runway with, just create a new item in the the `models` array, with the model's class name as the key and with a value like so:

```php
[
    'name' => 'Orders',
    'blueprint' => [...],
    'listing' => [...],
],
```

For each of the models, there's various configuration options available:

### `name`
This will be the name displayed throughout the Control Panel for this resource. We recommend you use a plural for this.

### `blueprint`
This is where you can define the fields & sections for your model's blueprint. You can use any available fieldtypes with any of their configuration options. You can optionally add validation rules if you'd like and they'll be used when saving or updating the record.

Make sure that you create a field for each of the required columns in your database or else you'll run into issues when saving. The handle for the field should match up with the column name in the database.

An example of a field configuration looks like this:

```php
'blueprint' => [
    'sections' => [
        'main' => [
            'fields' => [
                [
                    'handle' => 'title',
                    'field' => [
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                ],
                [
                    'handle' => 'body',
                    'field' => [
                        'type' => 'markdown',
                        'validate' => '',
                    ],
                ],
                [
                    'handle' => 'images',
                    'field' => [
                        'type' => 'assets',
                        'container' => 'assets',
                        'validate' => '',
                    ],
                ],
                [
                    'handle' => 'publish_at',
                    'field' => [
                        'type' => 'date',
                        'validate' => '',
                    ],
                ],
            ],
        ],
    ],
],
```

If you prefer, you can also create a normal blueprint file in `resources/blueprints` and reference it inside your config.

```php
'blueprint' => 'orders',
```

Bear in mind that at the moment, blueprints in the root of `resources/blueprint` won't be displayed as editable in the Control Panel.

### `hidden`

If you wish to hide a model from the Control Panel navigation, add the `hidden` key to your model in the Runway config.

```php
'hidden' => true,
```

### `listing`

Inside `listing`, you can control certain aspects of how the model's listing table displays records. You can currently configure the listing columns and the sort order of columns in the table.

```php
'listing' => [
    'columns' => [
        'order_number',
        'price',
    ],

    'sort' => [
        'column' => 'paid_at',
        'direction' => 'desc',
    ],

    'cp_icon' => 'icon-name-or-inline-svg',
],
```

## Usage

### Control Panel

At it's core, Runway provides Control Panel views for each of your models so you can view, create, update and delete Eloquent records. All the basic [CRUD](https://www.codecademy.com/articles/what-is-crud) actions you need.

### Templating

In addition to letting you create, view & update Eloquent records, Runway also provides a useful tag that allows you to output Eloquent records right in your front-end.

```antlers
{{ runway:post limit="5" }}
    <h2>{{ title }}</h2>
{{ /runway:post }}
```

In the above example, we are getting records from the `Post` model, and we are limiting the output to the first five records. The tag acts as, what is essentially a foreach loop, allowing you to output the same templating code for each record.

When looping through, you can access any of the fiels defined in your model's blueprint. Each field will also be ['augmented'](https://statamic.dev/extending/augmentation#what-is-augmentation), meaning you can use them the same way you can if you were using it inside an Entry.

The tag also has various parameters you can use to filter the records that get outputted. A list of parameters is provided below:

* `limit` - Allows you to define how many records you'd like to be output.
* `sort` - Define the column and order (descending or ascending) of records
* `where` - Get records where something is something else.

```handlebars
{{ runway:post sort="title:asc" where="author_id:duncan" limit="25" }}
    <h2>{{ title }}</h2>
{{ /runway:post }}
```

### Permissions

![Permissions](https://raw.githubusercontent.com/doublethreedigital/runway/master/permissions.png)

Runway provides some permissions to limit which users have access to view, create, edit and delete your model records. You can configure these permissions in the same way you can with built-in Statamic permissions. [Read the Statamic Docs](https://statamic.dev/users#permissions).

### Troubleshooting

**Unexpected data found. Trailing data**

Sometimes if you have a `date` or `datetime` column in your model, you may get an exception from Carbon regarding 'trailing data'. This can be sorted by casting the column to a `datetime` field in your Eloquent model, like so:

```php
protected $casts = [
    'publish_at' => 'datetime',
];
```

## Security

From a security perspective, the latest version only will receive a security release if a vulnerability is found.

If you discover a security vulnerability within Runway, please report it [via email](mailto:duncan@doublethree.digital) straight away. Please don't report security issues in the issue tracker.

## Resources

* [**Issue Tracker**](https://github.com/doublethreedigital/runway/issues): Find & report bugs in Runway
* [**Email**](mailto:duncan@doublethree.digital): Support from the developer behind the addon

---

<p>
<a href="https://statamic.com"><img src="https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge" alt="Compatible with Statamic v3"></a>
<a href="https://packagist.org/packages/doublethreedigital/runway/stats"><img src="https://img.shields.io/packagist/v/doublethreedigital/runway?style=for-the-badge" alt="Runway on Packagist"></a>
</p>
