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

TODO

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v6.x to v6.0](/upgrade-guides/v5-x-to-v6-0)

---

[You may also view a diff of changes between v4.x and v5.0](https://github.com/statamic-rad-pack/runway/compare/6.x...7.x)
