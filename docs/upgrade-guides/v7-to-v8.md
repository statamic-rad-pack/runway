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

TODO

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v6.x to v6.0](/upgrade-guides/v5-x-to-v6-0)
-   [v7.x to v8.0](/upgrade-guides/v7-to-v8)

---

[You may also view a diff of changes between v7.x and v8.0](https://github.com/statamic-rad-pack/runway/compare/7.x...8.x)
