---
title: 'Front-end Routing'
---

Front-end routing is honestly my favourite feature in Runway. It lets you setup front-end routes for your Eloquent models - behind the scenes, it works pretty much the exact same way as it does for entries.

## Enabling

> Before getting started, ensure you’ve run `php artisan migrate`. Runway comes with a `runway_uris` table which will be used to store all of the front-end URIs.

First things first, add a `route` key to your resource config, with the URI structure you want to use. Feel free to use Antlers in there for anything dynamic, like a slug or a date.

```php
// config/runway.php

'route' => '/products/{{ slug }}',
```

Next, add the `RunwayRoutes` trait to your Eloquent model.

```php
// app/Models/Product.php

use DuncanMcClean\Runway\Routing\Traits\RunwayRoutes;

class Product extends Model
{
    use RunwayRoutes;
```

Last but not least, run `php please runway:rebuild-uris`. This command will essentially loop through all of your models, compile the Antlers URIs, and save it to the database for reference later.

## Customising the template/layout used

Runway will assume you want to use the `default` template and the `layout` layout for resources.

However, most of the time, you’ll want to change this. To change it, just specify what you want to change them to.

```php
// config/runway.php

'template' => 'products.index',
'layout' => 'layouts.shop',
```

## Available variables

Instead your show/detail view of your model, you’ll have access to a bunch of variables:

-   All fields configured in your blueprint (with augmentation of course)
-   Any fields not in your blueprint but configured in your model, like `created_at`, `updated_at`.
-   Any other variables provided by [the Cascade](https://statamic.dev/cascade#content)

## Static Caching Invalidation

If you're taking advantage of Statamic's [Static Caching](https://statamic.dev/static-caching) functionality, Runway will automatically invalidate the URI of your models on save.

You may also configure additional URIs to be invalidated on save.

```php
// config/statamic/static_caching.php

'invalidation' => [

    'class' => null,

    'rules' => [
        'runway' => [
            'product' => [
                'urls' => [
                    '/products',
                    '/products/*',
                ],
            ],
        ],
    ],

],
```
