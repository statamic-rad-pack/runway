---
title: 'Blueprints'
---

As explained in the [Statamic Docs](https://statamic.dev/blueprints#content), Blueprints are a key component to the content modeling process. They let you define the fields that should be available in the Control Panel and the way your data is stored.

## Creating & managing blueprints

Every resource will have it's own blueprint. Just like with collections, you can manage the blueprints in the Control Panel.

![Runway blueprints in the Control Panel](/img/runway/runway-blueprints-in-the-cp.png)

When configuring fields, make sure that the field handles in your blueprint should match up *exactly* with the column names in the database, otherwise bad things will happen. You'll also want to ensure the database column type matches the fieldtype you're trying to use (see [Supported Fieldtypes](#supported-fieldtypes)).

## Supported Fieldtypes

Runway supports pretty much ALL fieldtypes available in Statamic, including Bard. As long as you have the correct fieldtype and the correct column type, everything should "just work"!

For simplicity, here's a table matching Statamic's Core fieldtypes with the correct column types:

**Fieldtype**|**Column Type**|**Notes**
-----|-----|-----
[Array](https://statamic.dev/fieldtypes/array)|`json`|
[Assets](https://statamic.dev/fieldtypes/assets)|`string`/`json`|
[Bard](https://statamic.dev/fieldtypes/bard)|`string`/`json`|If 'Display HTML' is `true`, then Bard will save as a `string`.
[Button Group](https://statamic.dev/fieldtypes/button_group)|`string`|
[Checkboxes](https://statamic.dev/fieldtypes/checkboxes)|`json`|
[Code](https://statamic.dev/fieldtypes/code)|`string`|
[Collections](https://statamic.dev/fieldtypes/collections)|`string`/`json`|If 'Allow multiple' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Color](https://statamic.dev/fieldtypes/color)|`string`|
[Date](https://statamic.dev/fieldtypes/date)|`string`/`range`|Format is specified field configuration options. Ranges are should be stored as json.
[Entries](https://statamic.dev/fieldtypes/entries)|`string`/`json`|If 'Allow multiple' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Fieldset](https://statamic.dev/fieldtypes/fieldset)|Depends on the fields being imported.|The columns depend on the fields being imported by your fieldset. You may import a fieldset using `import: fieldset_handle`. (the automatic migration generator does not support fieldsets)
Float|`float`|
[Grid](https://statamic.dev/fieldtypes/grid)|`json`|
[Hidden](https://statamic.dev/fieldtypes/hidden)|`string`|
[HTML](https://statamic.dev/fieldtypes/html)|-|UI only
[Integer](https://statamic.dev/fieldtypes/integer)|`integer`|
[Link](https://statamic.dev/fieldtypes/link)|`json`|
[List](https://statamic.dev/fieldtypes/list)|`json`|
[Markdown](https://statamic.dev/fieldtypes/markdown)|`string`|
[Radio](https://statamic.dev/fieldtypes/radio)|`string`|
[Range](https://statamic.dev/fieldtypes/range)|`string`|
[Replicator](https://statamic.dev/fieldtypes/replicator)|`json`|
[Revealer](https://statamic.dev/fieldtypes/revealer)|-|UI only
[Section](https://statamic.dev/fieldtypes/section)|-|UI only
[Select](https://statamic.dev/fieldtypes/select)|`string`/`integer`/`json`|
[Structures](https://statamic.dev/fieldtypes/structures)|`json`|
[Table](https://statamic.dev/fieldtypes/table)|`json`|
[Tags](https://statamic.dev/fieldtypes/tags)|`json`|
[Template](https://statamic.dev/fieldtypes/template)|`string`|
[Terms](https://statamic.dev/fieldtypes/terms)|`string`/`json`|
[Text](https://statamic.dev/fieldtypes/text)|`string`|
[Textarea](https://statamic.dev/fieldtypes/textarea)|`string`|
[Time](https://statamic.dev/fieldtypes/time)|`string`|
[Toggle](https://statamic.dev/fieldtypes/toggle)|`boolean`|
[Users](https://statamic.dev/fieldtypes/users)|`string`/`integer`/`json`|
[Video](https://statamic.dev/fieldtypes/video)|`string`|
[YAML](https://statamic.dev/fieldtypes/yaml)|`string`|
[Belongs To](/fieldtypes#belongsto-fieldtype)|`bigInteger`|Usually `bigInteger` or `integer` but depends on personal preference.

## Nesting fields inside JSON columns

To avoid creating a migration for every new field you add to a blueprint, fields can be stored within JSON columns. Simply use `->` within the field handle, like `values->excerpt`.

Your table will need to have a suitable column:

```php
$table->json('values')->nullable();
```

And the cast defined on the model:

```php
protected $casts = [
    'values' => 'array', // or 'json', AsArrayObject::class
];
```

:::note Note!
Nested Fields aren't currently available in GraphQL.
:::

## Generating migrations from your blueprints

If you’ve already went and created a blueprint for your model(s) and still to do the database side of things, Runway can help!

Runway can automatically generate migrations for your models, based on the fields defined in your blueprint, and their configurations.

To generate a migration for a specific blueprint:

```
php please runway:generate-migrations resource-handle
```

You may also run this same command for all resources pending a migration.

```
php please runway:generate-migrations
```

## Generating blueprints from your database

If you've already got an Eloquent model setup, Runway can help you turn it into a blueprint!

Before you can generate, you'll need to install the [`doctrine/dbal`](https://github.com/doctrine/dbal) package as it'll be used by Runway to analyse your database columns. You'll also need to have migrated your database already.

As well as having your model setup, you will also need to add the resource(s) to your `config/runway.php` config.

To generate a blueprint for a specific resource:

```
php please runway:generate-blueprints resource-handle
```

You may also run this same command for all resources:

```
php please runway:generate-blueprints
```

## Computed Fields

Like Statamic Core, Runway supports the concept of Computed Fields. However, instead of the computed values being part of a callback in your `AppServiceProvider`, they're accessors on your Eloquent model.

For example, if you wanted to have a `full_name` field that's computed based on the user's first & last name, you'd do something like this in your `User` model:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

public function fullName(): Attribute
{
    return Attribute::make(
        get: function () {
            return "{$this->first_name} {$this->last_name}";
        }
    );
}
```

Then, in your user blueprint, you'd set the field's visibility to "Computed":

![Field's visibility set to computed](/img/runway/field-visibility-computed.png)
