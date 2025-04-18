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

**Fieldtype**| **Column Type**          |**Notes**
-----|--------------------------|-----
[Array](https://statamic.dev/fieldtypes/array)| `json`                   |
Asset Container| `string`/`json`          |
[Assets](https://statamic.dev/fieldtypes/assets)| `string`/`json`          |
[Bard](https://statamic.dev/fieldtypes/bard)| `string`/`json`          |If 'Display HTML' is `true`, then Bard will save as a `string`.
[Button Group](https://statamic.dev/fieldtypes/button_group)| `string`                 |
[Checkboxes](https://statamic.dev/fieldtypes/checkboxes)| `json`                   |
[Code](https://statamic.dev/fieldtypes/code)| `string`                 |
[Collections](https://statamic.dev/fieldtypes/collections)| `string`/`json`          |If 'Max items' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Color](https://statamic.dev/fieldtypes/color)| `string`                 |
[Date](https://statamic.dev/fieldtypes/date)| `string`/`range`         |Format is specified field configuration options. Ranges are should be stored as json.
[Dictionary](https://statamic.dev/fieldtypes/dictionary)|`string`/`json`           |If 'Max items' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Entries](https://statamic.dev/fieldtypes/entries)| `string`/`json`          |If 'Max items' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Form](https://statamic.dev/fieldtypes/form)| `string`/`json`          |If 'Max items' is `1`, column type should be `string`. Otherwise, `json` is what you want.
[Grid](https://statamic.dev/fieldtypes/grid)| `json`                   |
[Group](https://statamic.dev/fieldtypes/group)| `json`                   |
[Hidden](https://statamic.dev/fieldtypes/hidden)| `string`                 |
[HTML](https://statamic.dev/fieldtypes/html)| -                        |UI only
[Icon](https://statamic.dev/fieldtypes/icon)| `string`                 |
[Integer](https://statamic.dev/fieldtypes/integer)| `integer`                |
[Link](https://statamic.dev/fieldtypes/link)| `json`                   |
[List](https://statamic.dev/fieldtypes/list)| `json`                   |
[Markdown](https://statamic.dev/fieldtypes/markdown)| `string`                 |
[Navs](https://statamic.dev/fieldtypes/navs)| `string`/`json`          |
[Radio](https://statamic.dev/fieldtypes/radio)| `string`                 |
[Range](https://statamic.dev/fieldtypes/range)| `string`                 |
[Replicator](https://statamic.dev/fieldtypes/replicator)| `json`                   |
[Revealer](https://statamic.dev/fieldtypes/revealer)| -                        |UI only
[Section](https://statamic.dev/fieldtypes/section)| -                        |UI only
[Select](https://statamic.dev/fieldtypes/select)| `string`/`integer`/`json` |
[Sites](https://statamic.dev/fieldtypes/sites)| `string`/`json`          |
[Slug](https://statamic.dev/fieldtypes/slug)| `string`                 |
[Spacer](https://statamic.dev/fieldtypes/spacer)| -                        |UI only
[Structures](https://statamic.dev/fieldtypes/structures)| `json`                   |
[Table](https://statamic.dev/fieldtypes/table)| `json`                   |
[Tags](https://statamic.dev/fieldtypes/tags)| `json`                   |
[Taxonomies](https://statamic.dev/fieldtypes/taxonomies)| `string`/`json`          |
[Template](https://statamic.dev/fieldtypes/template)| `string`                 |
[Terms](https://statamic.dev/fieldtypes/terms)| `string`/`json`          |
[Text](https://statamic.dev/fieldtypes/text)| `string`                 |
[Textarea](https://statamic.dev/fieldtypes/textarea)| `string`                 |
[Time](https://statamic.dev/fieldtypes/time)| `string`                 |
[Toggle](https://statamic.dev/fieldtypes/toggle)| `boolean`                |
[User Groups](https://statamic.dev/fieldtypes/user-groups)| `string`/`json`          |When the resource is the `User` model, you don't need to create a column for this fieldtype.
[User Roles](https://statamic.dev/fieldtypes/user-roles)| `string`/`json`          |When the resource is the `User` model, you don't need to create a column for this fieldtype.
[Users](https://statamic.dev/fieldtypes/users)| `string`/`integer`/`json` |
[Video](https://statamic.dev/fieldtypes/video)| `string`                 |
[Width](https://statamic.dev/fieldtypes/width)| `integer`                |
[YAML](https://statamic.dev/fieldtypes/yaml)| `string`                 |
 
## Eloquent Relationships

Runway provides two fieldtypes to let you manage Eloquent Relationships within Statamic:

* Belongs To
* Has Many

To find out more about Runway's fieldtypes, check out the [Fieldtypes](/fieldtypes) page.

## Nesting fields inside JSON columns

To avoid needing to create a migration for every new field you add to a blueprint, fields can be stored within JSON columns.

To do this, you'll first need to configure the JSON column under the `nested_field_prefixes` key in your `config/runway.php` config file.

```php
'resources' => [
    Order::class => [
        'nested_field_prefixes' => [ // [tl! ++]
            'address', // [tl! ++]
        ], // [tl! ++]
    ],
],
```

Then, when you're adding fields to your blueprint, simply prefix the column name, like shown below, and Runway will be smart enough to read/write from your JSON column. 🧠

```yaml
-
  handle: address_street_name # Represents the street_name key, in the address column.
  field:
    type: text
    display: 'Street Name'
```

**Heads up!**
In order for Nested Fields to work, you'll need to define a cast for the JSON column in your Eloquent model.

```php
protected function casts(): array
{
    return [
        'address' => 'array', // or 'json', AsArrayObject::class
    ];
}
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

:::note Note!
It's worth noting, Runway requires any accessors to be `public` functions, otherwise the attributes won't be augmentable.
:::
