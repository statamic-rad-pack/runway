![Banner](https://raw.githubusercontent.com/doublethreedigital/runway/master/banner.png)

## Runway

Runway allows you to easilly manage your Eloquent models straight from your Statamic Control Panel. Runway also gives you the option of outputting your Eloquent models in your Antlers templates. No need for a custom tag, it's all built-in.

This repository contains the source code of Runway. While Runway is free and doesn't require a license, you can [donate to Duncan](https://duncanmcclean.com/donate), the developer behind Runway, to show your appreciation.

## Installation

1. Install via Composer `composer require doublethreedigital/runway`
2. Publish the configuration file `php artisan vendor:publish --tag="runway-config"`
3. Configure each of the 'resources' you'd like to be available through Runway.

## Configuration

During installation, you'll publish a configuration file for Runway to `config/runway.php`. The contents of said file look like this:

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
        // \App\Models\Order::class => [
        //     'name' => 'Orders',

        //     'blueprint' => [
        //         'sections' => [
        //             'main' => [
        //                 'fields' => [
        //                     [
        //                         'handle' => 'price',
        //                         'field' => [
        //                             'type' => 'integer',
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

To configure the models you'd like to use Runway with, just create a new item in the the `resources` array, with the model's class name as the key and with a value like so:

```php
[
    'name' => 'Orders',
    'blueprint' => [...],
    'listing' => [...],
],
```

For each of the resources, there's various configuration options available:

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

If you wish to hide a resource from the Control Panel navigation, add the `hidden` key to the resource in your config.

```php
'hidden' => true,
```

### `listing`

Inside `listing`, you can control certain aspects of how the resource's listing table displays records. You can currently configure the listing columns and the sort order of columns in the table.

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

#### Listing buttons

**In the future, the plan is to replace this concept with [Actions](https://statamic.dev/extending/actions#content), the same way it works for collections. This means this feature will probably be removed in future versions.**

If you need to add some sort of button to your resource listing page, like for a CSV export or something similar, you can add your own 'listing button'.

![Banner](https://raw.githubusercontent.com/doublethreedigital/runway/master/listing-buttons.png)

```php
'listing' => [
    ...

    'buttons' => [
        'Export as CSV' => YourController::class,
    ],
],
```

When a user clicks the button, it will run the specified controller's `__invoke` method. Make sure to add any logic you need into there!

```php
use DoubleThreeDigital\Runway\Resource;
use Illuminate\Http\Request;

class YourController extends Controller
{
    public function __invoke(Request $request, Resource $resource)
    {
        // Your code..
    }
}
```

## Usage

### Control Panel

At it's core, Runway provides Control Panel views for each of your resources so you can view, create, update and delete Eloquent records. All the basic [CRUD](https://www.codecademy.com/articles/what-is-crud) actions you need.

### Routing

Ever found yourself in a situation where you just want to display one of your Runway resources on the front-end of your Statamic site and have them treated exactly like entries? Well, that's exactly where I found myself, so I built it...

The routing feature is purley optional and can be configured on 'resource by resource' basis. Meaning you can have routing enabled on one resource but not another.

#### Enabling routing

> Before getting started, please ensure you've ran `php artisan migrate`. Runway will need to migrate a table to store compiled URIs.

First things first, you'll need to add a `route` configuration option to your resource with the URI structure you wish to use for the resource. You can use Antlers for any dynamic segments you need, like for a slug or for a date.

```php
// config/runway.php

'route' => '/products/{{ slug }}',
```

Next, you'll need to add the `RunwayRoutes` trait and the `Responsable` interface to your Eloquent model.

```php
// app/Models/Product.php

use DoubleThreeDigital\Runway\Routing\Traits\RunwayRoutes;
use Illuminate\Contracts\Support\Responsable;

class Product extends Model implements Responsable
{
    use RunwayRoutes;
```

Last but not least, please run `php please runway:rebuild-uris`. This command will loop through all your models and build a cache of compiled URIs based on your URI structure (you'll find these in your `runway_uris` table).

#### Changing the template/layout used

By default, Runway will assume you want to use `default` and `layout` as your template and layout respectively. However, this isn't always the case so there's configuration options for both.

```php
// config/runway.php

'template' => 'products.index',
'layout' => 'layouts.shop',
```

#### Available variables

Inside your view, you'll have access to:

* All of the fields configured in your blueprint (with augmentation of course)
* Any fields not in your blueprint but configured in your model, like `created_at` and `updated_at`
* Any variables provided by [the Cascade](https://statamic.dev/cascade#content)
* `url` - the URL of the current page

### Antlers Tag

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

Additionally, if you're using Runway's routing feature, a `url` variable will be automatically added alongside your augmented variables. Allowing you to do something like this:

```handlebars
<h2>Blog Posts</h2>

{{ runway:post }}
    <h2><a href="{{ url }}">{{ title }}</a></h2>
{{ /runway:post }}
```

#### Scoping

The Runway tag also allows you to scope your results to a certain variable, similar to how [scoping works on the collection tag](https://statamic.dev/tags/collection#scope).

```handlebars
{{ runway:post as="posts" }}
    {{ posts }}
        <h2>{{ title }}</h2>
    {{ /posts }}
{{ /runway:post }}
```

#### Pagination

You can also use pagination with the Runway tag if you need to. Bear in mind, you'll also need to use scoping while also using pagination.

```handlebars
{{ runway:post as="posts" paginate="true" limit="10" }}
    {{ if no_results }}
        <p>Nothing has been posted yet. Sad times.</p>
    {{ /if }}

    {{ posts }}
        <h2>{{ title }}</h2>
    {{ /posts }}

    {{ paginate }}
        {{ if prev_page_url }}
            <a href="{{ prev_page_url }}">Previous</a>
        {{ /if }}

        <span>Page {{ current_page }} of {{ last_page }}</span>

        {{ if next_page_url }}
            <a href="{{ next_page_url }}">Previous</a>
        {{ /if }}
    {{ /paginate }}
{{ /runway:post }}
```

### Fieldtypes

#### BelongsTo fieldtype

![BelongsTo Fieldtype](https://raw.githubusercontent.com/doublethreedigital/runway/master/belongs-to-fieldtype.png)

Recently, a Belongs To fieldtype has been added to Runway. It allows you to select a record from a specified resource. The record's primary key will then be saved.

You can use the BelongsTo fieldtype in any blueprint. Whether it be inside an entry or inside a blueprint you're using for a Runway resource, it should all work.

### Permissions

![Permissions](https://raw.githubusercontent.com/doublethreedigital/runway/master/permissions.png)

Runway provides some permissions to limit which users have access to view, create, edit and delete your records. You can configure these permissions in the same way you can with built-in Statamic permissions. [Read the Statamic Docs](https://statamic.dev/users#permissions).

### Knowledge Base

**What's the difference between Runway and the Eloquent driver?**

This is a fairly common question I get asked so thought I'd address it here once and for all.

Essentially, the [Eloquent driver](https://github.com/statamic/eloquent-driver) allows you to switch out ALL of your collections & entries to the database. There's no way to only move X but not move Y, it's either all or nothing.

Whereas Runway allows you to specifically choose what you want in the database and you can leverage the beast that is Eloquent. Personally, I've used Runway in Statamic sites that I've added extra functionality to, like a payment form. For security reasons, I've kept the payments in the database. It's worked great for that as well!

**Using Bard**

Runway should work with pretty much any Statamic fieldtype, including [Bard](https://statamic.dev/fieldtypes/bard#content).

However, as Bard saves as an array, there's a couple of extra steps you'll need to make in your Eloquent model.

1. Setup your Bard field in your blueprint.
2. Create a column for your Bard field, using the `json` column type is recommended.

```php
$table->json('body')->nullable();
```

3. In your Eloquent model, cast the column to JSON.

```php
protected $casts = [
    'body' => 'json',
];
```

> The above documentation on Bard also applies for any other fieldtypes that output arrays. Such as the Array fieldtype, the Grid fieldtype and the Replicator fieldtype.

**What's the `runway_uris` table for?**

The `runway_uris` table is created when you install Runway. It's used as a 'lookup table' for Runway's front-end routing feature.

Essentially, because we allow you to use Antlers to build your URI structure, we need somewhere to store the parsed versions of those for each model. The `runway_uris` table stores the parsed version of the URI along with the related model.

If you're not using the Runway's front-end routing feature, you may disable the migrations in your config.

```php
/*
|--------------------------------------------------------------------------
| Disable Migrations?
|--------------------------------------------------------------------------
|
| Should Runway's migrations be disabled?
| (eg. not automatically run when you next vendor:publish)
|
*/

'disable_migrations' => false,
```

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
