---
title: 'Upgrade Guide: v5.x to v6.0'
---

## Overview

:::warning Warning
Please don't upgrade multiple versions at once (eg. from v4 to v6). Please upgrade one step at a time.
:::

To get started with the upgrade process, follow the below steps:

**1.** As Runway is now part of [The Rad Pack](https://github.com/statamic-rad-pack), you'll need to uninstall the addon under its old name and re-install under the Rad Pack:

```sh
composer remove doublethreedigital/runway
composer require statamic-rad-pack/runway:^6.0
```

**2.** Next, you'll need to update references to Runway's traits in your Eloquent models.

```php
<?php

use Illuminate\Database\Eloquent\Model;
use DoubleThreeDigital\Runway\Traits\HasRunwayResource; // [tl! remove]
use StatamicRadPack\Runway\Traits\HasRunwayResource; // [tl! add]
use DoubleThreeDigital\Runway\Routing\Traits\RunwayRoutes; // [tl! remove]
use StatamicRadPack\Runway\Routing\Traits\RunwayRoutes; // [tl! add]

class Order extends Model
{
    use HasRunwayResource, RunwayRoutes;
}
```

**3.** After this step, clear your route & view caches to make sure everything is fresh:

```
php artisan route:clear
php artisan view:clear
```

**4.** **You've now updated to Runway v6!** Please review the changes in this guide and make any necessary changes to your site.

**Please test locally before deploying to production!**

## Changes
### High: Blueprints work differently
**You can now manage blueprints directly from the Control Panel, gone are the days of managing huge arrays or copying YAML from collection blueprints. 🚀**

As part of this, blueprints will now live as YAML files in the `resources/blueprints/vendor/runway` directory.

During the upgrade process, Runway *should* copy your existing blueprints into this new location. If it did not, run this command:

```
php please runway:migrate-blueprints
```

After your blueprints have been copied, you may remove the `blueprint` key from your resource configs in `config/runway.php` and delete any old blueprint files.

### Medium: Augmentation
The way augmentations works behind the scenes has changed in Runway v6 to bring it inline with how Statamic Core does augmentation.

You'll only need to take action if you're manually calling Runway's augmentation methods. If you are, you'll need to refactor them to call the `toAugmentedArray` method on your Eloquent model:

```php
// Previously....
$resource->augment($model);
AugmentedModel::augment($model, $resource->blueprint());

// Now..
$model->toAugmentedArray();
```

The output of `->toAugmentedArray()` will be slightly different to what was returned previously, with all fields now returning `Value` objects.

### Medium: Has Many  Fieldtype - Table Mode removed
Runway previously included a special "Table" mode for the Has Many fieldtype. However, the Table mode has been removed to help reduce complexity of Runway's fieldtypes.

If you were using the Table mode on any of your Has Many fields, you should switch the `mode` to `default`.

**Don't know if you're using the Table mode?** You can find out by searching these terms in your code editor of choice:

* `mode: table`
* `'mode' => 'table'`

### Low: Changes to overriding eager loaded relationships.
If you were previously overriding the "eager loaded relationships" in your resource's config array, you should change the key from `with` to `relationships`:

```php
\App\Models\Product::class => [
    // ...

    // Previously...
    'with' => ['tags', 'manufacturer'],

    // Now...
    'relationships' => ['tags', 'manufacturer'],
],
```

## Previous upgrade guides

-   [v3.x to v4.0](/upgrade-guides/v3-x-to-v4-0)
-   [v4.x to v5.0](/upgrade-guides/v4-x-to-v5-0)

---

[You may also view a diff of changes between v4.x and v5.0](https://github.com/statamic-rad-pack/runway/compare/5.x...6.x)
