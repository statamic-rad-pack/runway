---
title: 'Upgrade Guide: v7.x to v8.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple major versions at once (eg. from v6 to v8). You should upgrade one major version at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, change the `statamic-rad-pack/runway` version constraint to `^8.0`:

```json
"statamic-rad-pack/runway": "^8.0"
```

**2.** Then run:

```
composer update statamic-rad-pack/runway --with-dependencies
```

**3.** Next, please ensure you have cleared the route and view caches:

```
php artisan route:clear
php artisan view:clear
```

**4.** You're now running Runway v8. Please review this upgrade guide for information on changes which may affect your project.

**Please test your project locally before deploying to production!**

## High impact changes

### Changes to Nested Fields
**Affects apps using nested JSON fields.**

In previous versions of Runway, nested fields were configured using the `->` separator.

However, in Statamic 5, validation around field handles has been tightened up, and `>` is no longer considered a valid character in field handles. 

To work around this, v8 introduces some changes around how nested fields are configured:

1. Instead of using `->` to separate the column name and the JSON key in field handles, you should now use an underscore:
    ```yaml
    -
      handle: address->street_name # [tl! remove]
      handle: address_street_name # [tl! add]
      field:
        type: text
        display: 'Street Name'
   ```
   
2. You should also specify the "nested field prefixes" (eg. the JSON column names) in your Runway config file. This will allow Runway to determine which fields are nested.

    ```php
    Order::class => [
        'nested_field_prefixes' => [
            'address',
        ],
    ],
    ```
   
As an upside of this new approach, nested fields can now be used with Runway's [GraphQL API](/graphql).

## Low impact changes

### Removal of the `cp_icon` config option

The `cp_icon` configuration option has been removed in Runway 8, in favour of being able to change the icon using Statamic's [Nav Preferences](https://statamic.dev/preferences#accessing-preferences) feature. 

```php
'resources' => [
	\App\Models\Order::class => [
        'name' => 'Orders',
        'cp_icon' => 'date', // [tl! --]
	],
],
```

### Generate Migrations command has been removed

The `runway:generate-migrations` command has been removed in favour of the new `runway:import-collection` command which handles the entire process of generating Eloquent models, database migrations and importing entries.

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v5.x to v6.0](/upgrade-guides/v5-x-to-v6-0)
-   [v6.x to v7.0](/upgrade-guides/v6-to-v7)

---

[You may also view a diff of changes between v7.x and v8.0](https://github.com/statamic-rad-pack/runway/compare/7.x...8.x)
