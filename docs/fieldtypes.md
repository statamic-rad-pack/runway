---
title: Fieldtypes
---

Runway provides two fieldtypes to let you manage Eloquent relationships within Statamic: Belongs To & Has Many.

You can not only use these fieldtypes to represent Eloquent relationships on your models but you can also use them to **relate Eloquent models to entries/terms**, it's almost like magic 🪄.

## Belongs To

You can use the **Belongs To** fieldtype to relate to a single Eloquent model.

To configure the Belongs To fieldtype, you'll need to ensure you've created the [`belongsTo` relationship](https://laravel.com/docs/master/eloquent-relationships#one-to-many-inverse) on your model:

```php
// app/Models/Post.php

public function author()
{
    return $this->belongsTo(Author::class);
}
```

You'll next need to add the field to your resource's blueprint:

```yaml
# resources/blueprints/post.yaml

-
    handle: author_id
    field:
        max_items: 1
        mode: default
        resource: author
        create: true
        display: 'Author'
        type: 'belongs_to'
```

Make sure `resource` is set to the handle of the 'related resource' (in this case, the one for the `Author` model). You should also ensure the handle of the field is set to the name of the Belongs To column in the database, like `author_id`.

:::note Note!
When using this fieldtype on Entries / Terms, you don't need to create an Eloquent relationship and you can name your field anything (it doesn't need to be in the format `model_id`).
:::


## HasMany fieldtype

![Screenshot of the Has Many Fieldtype](/img/runway/has-many-fieldtype.png)

You can use the **Has Many** fieldtype to relate to multiple Eloquent models.

To configure the Has Many fieldtype, you'll need to ensure you've created a [`hasMany`/`morphedByMany` relationship](https://laravel.com/docs/master/eloquent-factories#has-many-relationships) on your model:

```php
// app/Models/Post.php

public function categories()
{
    return $this->hasMany(Category::class);
}
```

You'll next need to add the field to your resource's blueprint:

```yaml
# resources/blueprints/post.yaml

-
    handle: categories
    field:
        type: has_many
        resource: category
        mode: default
        display: Categories
```

Make sure `resource` is set to the handle of the 'related resource' (in this case, the one for the `Category` model). You should also ensure the handle of the field is the same as the name of the Eloquent relationship.

:::note Note!
When using this fieldtype on Entries / Terms, you obviously don't need to create an Eloquent relationship so you can name your field anything.
:::

### Re-ordering relationships

If you'd like users to be able to re-orderable models in your Has Many field, you may set `reorderable` to `true` and specify the column you'd like to use to store the order.

If you're using a pivot table for the relationship, the order column must exist on the pivot table.

```yaml
# resources/blueprints/post.yaml

-
    handle: categories
    field:
        type: has_many
        resource: category
        mode: default
        display: Categories
        orderable: true // [tl! add]
        order_column: sort_order  // [tl! add]
```

### Table Mode

By default, [Relationship Fieldtypes](https://statamic.dev/extending/relationship-fieldtypes#content) in Statamic have three modes: `default`/`select`/`typeahead`.

However, the Has Many fieldtype offers an additional `table` mode. This displays the related Eloquent models in a table (similar to listing tables used elsewhere in the Control Panel), allowing you to see more information about each model.

![Table Mode](/img/runway/table-mode.png)

You may enable Table Mode by adjusting the `mode` configuration option:

```yaml
# resources/blueprints/post.yaml

-
    handle: categories
    field:
        type: has_many
        resource: category
        mode: default // [tl! remove]
        mode: table // [tl! add]
        display: Categories
```

## Title Format

The `title_format` configuration option allows you to specify a title format to be used when related models are displayed in the Control Panel.

You can even use Antlers when specifying a `title_format`.

```yaml
# resources/blueprints/post.yaml

-
    handle: categories
    field:
        type: has_many
        resource: category
        mode: default
        display: Categories
        title_format: 'Category ID {{ id }}: {{ name }}' // [tl! add]
```

### BelongsToMany

You can use the HasMany fieldtype to handle `belongsToMany` relationships. Simply add a HasMany fieldtype to both sides of the relationship and you'll be golden!

## Eager Loading

If you wish, you can configure additional relationships to be eager loaded with the field:

```yaml
# resources/blueprints/post.yaml

-
    handle: categories
    field:
        type: has_many
        resource: category
        mode: default
        display: Categories
        with:
            - 'parentCategory'
```
