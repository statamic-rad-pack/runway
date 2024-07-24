---
title: Fieldtypes
---

Runway provides two fieldtypes to let you manage Eloquent Relationships within Statamic:

* Belongs To
  * Useful for `belongsTo` relationships.
* Has Many
  * Useful for `hasMany` & `belongsToMany` relationships. 

You can also use these fieldtypes in Entry & Term blueprints, to relate entries/terms to Eloquent models.

## Belongs To

The **Belongs To** fieldtype allows you to relate to a single Eloquent model.

When used on a Runway blueprint, you should ensure that the field handle matches the column name in the database.

### Templating

In Antlers, you can access any of the fields on the model. They'll be [augmented](https://statamic.dev/extending/augmentation) using the resource's blueprint.

```antlers
Written by {{ author:first_name }} {{ author:last_name }} ({{ author:location }})
```

### Options

| **Option**          | **Description**                                                                                                                              |
|---------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| `mode`              | Set the UI style for this field. Can be one of 'default' (Stack  Selector), 'select' (Select Dropdown) or 'typeahead' (Typeahead Field).     |
| `resource`          | Specify the Runway Resource to be used for this field.                                                                                       |
| `relationship_name` | The name of the Eloquent Relationship this field should use. When left empty, Runway will attempt to guess it based on the field's handle.   |
| `create`            | By default you may create new models. Set to `false` to only allow selecting from existing models.                                           |
| `with`              | Specify any relationships you want to be eager loaded when this field is augmented. This option accepts an array of relationships.           |
| `title_format`      | Configure the title format used for displaying results in the fieldtype. You can use Antlers to pull in model data.                          |

## Has Many

![Screenshot of the Has Many Fieldtype](/img/runway/has-many-fieldtype.png)

The **Has Many** fieldtype allows you to relate to multiple Eloquent models.

When used on a Runway blueprint, you should ensure the field handle matches the name of the Eloquent relationship in your model.

:::note Note!
Statamic doesn't support camel case field handles, which means that in some cases it may not be possible to match the field handle with the name of the Eloquent relationship.

In such case, you can either:
* Use snake case for the field handle and Runway will be smart enough to figure out which relationship you're trying to use (for example: the `featured_posts` field handle will be used to relate to the `featuredPosts` relationship).
* Use the `relationship_name` option to specify the name of the Eloquent relationship.
:::

### Templating

Loop through the models and do anything you want with the data.

```antlers
<ul>
    {{ related_posts }}
        <li><a href="{{ url }}">{{ title }}</a></li>
    {{ /related_posts }}
</ul>
```

### Options

| **Option**          | **Description**                                                                                                                                                                               |
|---------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `mode`              | Set the UI style for this field. Can be one of 'default' (Stack  Selector), 'select' (Select Dropdown) or 'typeahead' (Typeahead Field).                                                      |
| `resource`          | Specify the Runway Resource to be used for this field.                                                                                                                                        |
| `relationship_name` | The name of the Eloquent Relationship this field should use. When left empty, Runway will attempt to guess it based on the field's handle.                                                    |
| `create`            | By default you may create new models. Set to `false` to only allow selecting from existing models.                                                                                            |
| `with`              | Specify any relationships you want to be eager loaded when this field is augmented. This option accepts an array of relationships.                                                            |
| `title_format`      | Configure the title format used for displaying results in the fieldtype. You can use Antlers to pull in model data.                                                                           |
| `reorderable`       | Determines whether the models can be reordered. Defaults to `false`.                                                                                                                          |
| `order_column`      | When reordering is enabled, this determines which column should be used for storing the sort order. When the relationship uses a pivot table, the order column must exist on the pivot table. |
