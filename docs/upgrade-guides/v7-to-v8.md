---
title: 'Upgrade Guide: v7.x to v8.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v6 to v8). Please upgrade one step at a time.
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
      handle: address_street_name # // [tl! add]
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

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v6.x to v6.0](/upgrade-guides/v5-x-to-v6-0)
-   [v7.x to v8.0](/upgrade-guides/v7-to-v8)

---

[You may also view a diff of changes between v7.x and v8.0](https://github.com/statamic-rad-pack/runway/compare/7.x...8.x)
