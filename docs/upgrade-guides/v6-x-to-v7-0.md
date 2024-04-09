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

## Changes

## High: Statamic support
**Affects all apps using Runway**

To avoid any compatibility issues, Runway 7 only supports Statamic 5. Please review the [Statamic 5 upgrade guide](https://statamic.dev/upgrade-guide/4-0-to-5-0).

### PHP and Laravel support
**Affects apps using PHP < 8.2 or Laravel < 10.**

- The minimum version of PHP is now 8.1.
- The minimum version of Laravel is now 10.

We highly recommend upgrading all the way to Laravel 11 and PHP 8.3.




### High: Support for Statamic 4 has been dropped

Runway v7 has dropped support for Statamic 4. In order to update Runway, you will need to [upgrade to Statamic 5](https://statamic.dev/upgrade-guide/4-0-to-5-0).

By keeping up-to-date with major Statamic versions, it removes the risk of any compatibility issues between Statamic 4 and 5.

### High: Support for Laravel 10 has been dropped

Runway v7 has dropped support for Laravel 10. In order to update Runway, you will need to [upgrade to Laravel 11](https://laravel.com/docs/11.x/upgrade).

If you wish, you can automate the upgrade process using [Laravel Shift](https://laravelshift.com/upgrade-laravel-10-to-laravel-11).

### Medium: Support for PHP 8.1 has been dropped

Runway v7 has dropped support for PHP 8.1. In order to update Runway, you should update the version of PHP you're using, both locally and on production. The latest version of PHP is 8.3.

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v6.x to v6.0](/upgrade-guides/v5-x-to-v6-0)

---

[You may also view a diff of changes between v4.x and v5.0](https://github.com/statamic-rad-pack/runway/compare/6.x...7.x)
