---
title: 'Upgrade Guide: v8.x to v9.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple major versions at once (eg. from v7 to v9). You should upgrade one major version at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** In your `composer.json` file, change the `statamic-rad-pack/runway` version constraint to `^9.0`:

```json
"statamic-rad-pack/runway": "^9.0"
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

**4.** Carefully review this upgrade guide for changes which may affect your project.

## High impact changes


## Low impact changes


## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)
-   [v5.x to v6.0](/upgrade-guides/v5-x-to-v6-0)
-   [v6.x to v7.0](/upgrade-guides/v6-to-v7)
-   [v7.x to v8.0](/upgrade-guides/v7-to-v8)

---

[You may also view a diff of changes between v8.x and v9.0](https://github.com/statamic-rad-pack/runway/compare/8.x...9.x)
