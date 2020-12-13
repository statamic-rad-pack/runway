# Runway

This addon allows you to easily manage your Eloquent models and display them in your templates. It includes Control Panel listing tables and publish forms.

Although the code for this addon is open-source, you need to purchase a license from the Statamic Marketplace to use it on [a public domain](https://statamic.dev/licensing#public-domains).

## Installation

1. Install via Composer `composer require doublethreedigital/runway`
2. Publish the configuration file `php artisan vendor:publish --tag="runway-config"`
3. Configure the fields for each of the Eloquent models you wish to be used with Runway.

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
        //     ],
        // ],
    ],

];
```

To configure the models you'd like to use Runway with, just create a new item in the the `models` array, with the model's class name as the key and with a value like so:

```php
[
    'name' => 'Orders',
    'fields' => [],
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

While these fields create a blueprint for the publish forms, it should be pointed out that no blueprint will be saved as a file to your `resources/blueprints` directory. It's created on the fly.

### `listing`

Inside `listing`, you can control certain aspects of how the model's listing table works. Currently, the only thing you can customize on the table are the columns that are displayed.

```php
'listing' => [
    'columns' => [
        'order_number',
        'price',
    ],
],
```

You can display as many of the fields from your blueprint as you need.

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

```antlers
{{ runway:post sort="title:asc" where="author_id:duncan" limit="25" }}
    <h2>{{ title }}</h2>
{{ /runway:post }}
```

## Troubleshooting

**Unexpected data found. Trailing data**

Sometimes if you have a `date` or `datetime` column in your model, you may get an exception from Carbon regarding 'trailing data'. This can be sorted by casting the column to a `datetime` field in your Eloquent model, like so:

```php
protected $casts = [
    'publish_at' => 'datetime',
];
```

## Roadmap

We've got a couple of features we're planning on implementing in the next couple of months. If you've got any additional feature requests, please create an issue for them.

* Filtering & Search on the CP Listing table
* Control Panel Permissions
* Ability to define custom actions

## Support
For developer support or any other questions related to this addon, please [get in touch](mailto:hello@doublethree.digital).
