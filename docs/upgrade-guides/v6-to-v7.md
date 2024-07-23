---
title: 'Upgrade Guide: v6.x to v7.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v5 to v7). Please upgrade one step at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, change the `statamic-rad-pack/runway` version constraint to `^7.0`:

```json
"statamic-rad-pack/runway": "^7.0"
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

**4.** You're now running Runway v7. Please review this upgrade guide for information on changes which may affect your project.

**Please test your project locally before deploying to production!**

## High impact changes

## Statamic support
**Affects all apps using Runway**

The minimum version of Statamic is now 5. Please review the [Statamic 5 upgrade guide](https://statamic.dev/upgrade-guide/4-0-to-5-0).

### PHP support
**Affects apps using PHP 8.1**

The minimum version of PHP is now 8.2. We highly recommend upgrading all the way to PHP 8.3.

### Resource handles are now generated differently
**Affects apps with Eloquent models, where the class name is multiple words**

Runway will now generate resource handles slightly differently for Eloquent models, where the class name is multiple words.

For example: in v6, the resource handle for a model named `BlogPost` would have been `blogpost`. In v7, it will now be `blog_post` for easier readability.

If this affects you, you can either update all references to the old resource handle in your blueprints & templates, or manually override the handle of the resource in your Runway config:

```php
// config/runway.php

BlogPost::class => [
    'handle' => 'blogpost',
],
```

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v6.x to v6.0](/upgrade-guides/v5-x-to-v6-0)

---

[You may also view a diff of changes between v6.x and v7.0](https://github.com/statamic-rad-pack/runway/compare/6.x...7.x)
