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
    ],

];
```

To configure the models you'd like to use Runway with, just create a new item in the the `resources` array, with the model's class name as the key and with a value like so:

```php
[
    'name' => 'Orders',
    'blueprint' => [...],
],
```

### Available configuration options

For each of the resources, there's various configuration options available:

#### `name`

This will be the name displayed throughout the Control Panel for this resource. We recommend you use a plural for this.

#### `blueprint`

The `blueprint` option allows you to define the fields you'd like Runway to make available (and augment) in your Listing views, Publish Forms and via the front-end routing feature. An example config may look similar to this:

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

If you'd prefer to keep your blueprint out of the config, you may instead specify the 'namespace' of a blueprint (it's location in `resources/blueprints`). These blueprint files are not currently editable inside the Control Panel.

```php
'blueprint' => 'orders',
```

Make sure that the handles of the fields are the same as the column names in your database. You can use pretty much any fieldtype you wish with Runway, you'll just need to make sure the column types match out with what's outputted by Statamic/Runway. We've provided a mapping table below that'll tell you which column types to use for different fieldtypes.

| Fieldtype    | Column Type                                  | Notes                                                                                                                                                                            |
|--------------|----------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Array        | json                                         |                                                                                                                                                                                  |
| Assets       | string/json                                  |                                                                                                                                                                                  |
| Bard         | json/string                                  | string if 'Display HTML' is true                                                                                                                                                 |
| Button Group | string                                       |                                                                                                                                                                                  |
| Checkboxes   | json                                         |                                                                                                                                                                                  |
| Code         | string                                       |                                                                                                                                                                                  |
| Collections  | string/json                                  | allow multiple / max 1                                                                                                                                                           |
| Color        | string                                       |                                                                                                                                                                                  |
| Date         | string/range                                 | Format is specified field configuration options. Ranges are should be stored as json.                                                                                            |
| Entries      | string/json                                  | allow multiple / max 1                                                                                                                                                           |
| Fieldset     | depends on field being imported by field set | You can import a fieldset into runway using 'import' => 'fieldset_handle' as a field. It is your responsibvility to match the fieldtypes within that field set to your migration |
| Float        | float                                        |                                                                                                                                                                                  |
| Grid         | json                                         |                                                                                                                                                                                  |
| Hidden       | string                                       |                                                                                                                                                                                  |
| HTML         | -                                            | UI only                                                                                                                                                                          |
| Integer      | integer                                      |                                                                                                                                                                                  |
| Link         | json                                         |                                                                                                                                                                                  |
| List         | json                                         |                                                                                                                                                                                  |
| Markdown     | string                                       |                                                                                                                                                                                  |
| Radio        | string                                       |                                                                                                                                                                                  |
| Range        | string                                       |                                                                                                                                                                                  |
| Replicator   | json                                         |                                                                                                                                                                                  |
| Revealer     | -                                            | UI only                                                                                                                                                                          |
| Section      | -                                            | UI only                                                                                                                                                                          |
| Select       | string/integer/json                          |                                                                                                                                                                                  |
| Structures   | json                                         |                                                                                                                                                                                  |
| Table        | json                                         |                                                                                                                                                                                  |
| Tags         | json                                         |                                                                                                                                                                                  |
| Template     | string                                       |                                                                                                                                                                                  |
| Terms        | string/json                                  |                                                                                                                                                                                  |
| Text         | string                                       |                                                                                                                                                                                  |
| Textarea     | string                                       |                                                                                                                                                                                  |
| Time         | string                                       |                                                                                                                                                                                  |
| Toggle       | boolean                                      |                                                                                                                                                                                  |
| Users        | string/integer/json                          |                                                                                                                                                                                  |
| Video        | string                                       |                                                                                                                                                                                  |
| YAML         | string                                       |                                                                                                                                                                                  |
| Belongs To   | integer/string                               | Usually a bigInteger, but depends on your eloquent relationship definitions.                                                                                                     |

When writing your migrations, please ensure that fields not required in your blueprint should be `->nullable()` in the migration. Otherwise, you'll end up with a nasty error.

As a general rule of thumb, if something saves as an array when saving as YAML, you should probably use a `json` column and cast that in your model, like below:

##### Generating a migration from your blueprint

If you already have a blueprint and you'd like to generate a migration from the fields in there, you can use the following command:

```
php please runway:generate-migrations resource-handle
```

If you'd like to generate for all resources, you can use the following:

```
php please runway:generate-migrations
```

This command will generate the right columns based on the `max_items` configuration on fields, the defaults set and whether or not fields have the `required` validation rule.

**Bard, Array, Grid, Replicator fieldtypes**

Bard, Array, Grid, Replicator all save as an array in Statamic flat files, so these fieldtypes should be set as a JSON column in your Laravel migration.

1. Setup your field in your config or blueprint.
2. Create a column in your migration for your field using the `json` column type:

```php
$table->json('my_bard_content')->nullable();
```

3. In your Eloquent model, cast the column to JSON.

```php
protected $casts = [
    'my_bard_content' => 'json',
];
```

#### `hidden`

If you wish to hide a resource from the Control Panel navigation, add the `hidden` key to the resource in your config.

```php
'hidden' => true,
```

#### `listing`

You may also customise certain aspects of the CP Listing, like the icon used in the navigation.

```php
'listing' => [
    'cp_icon' => 'icon-name-or-inline-svg',
],
```

## Usage

### Control Panel

At it's core, Runway provides the ability for you to view, create and update Eloquent models. Basically all of the CRUD stuff that you need.

Unless you've disabled it, each of your models will display in the CP Navigation. Clicking on one of them will show you a listing table, pretty similar to the one used for collection entries. You can search, set filters, show specific columns. All of that good stuff.

#### Actions

Runway provides full support for [Actions](https://statamic.dev/extending/actions#content) which allow you to preform tasks on items, using the 'three dots' button the right hand side of the listing row.

By default, you'll see a 'View', 'Edit' and 'Delete' button there but you can add more if you wish. Documentation on using Actions can be found on [statamic.dev](https://statamic.dev/extending/actions#content).

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

**How do I add a custom CP route for the Runway Resource?**

1. Make sure your resource is configured to be `'hidden' => true`.
2. Then in your `app\Providers\AppServiceProvider.php` add the following code:

```php
    //...

    public function boot()
    {
        Nav::extend(function ($nav) {
            $nav->EloquentContent('My Model')
                ->route('runway.index', ['resourceHandle' => 'mymodel']) // resourceHandle is the lowercase Class name of your model e.g. MyModel is mymodel
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                </svg>');
        });

        //...
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
