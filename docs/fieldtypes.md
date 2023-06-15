---
title: Fieldtypes
---

Runway provides a couple of fieldtypes, mostly for things like Eloquent relations.

## BelongsTo fieldtype

![Screenshot of BelongsTo fieldtype](/img/runway/belongs-to-fieldtype.png)

This fieldtypes allows you to select a single model for a `belongsTo` Eloquent relationship.

### Configuration

It’s important that when configuring this fieldtype, the handle of the field is the same as the `belongsTo` column name, eg: `category_id`.

Also when configuring the fieldtype, you may choose the resource you wish to be available for selection by the user.

## HasMany fieldtype

This fieldtype allows you to select multiple models for a `hasMany` or `morphedByMany` Eloquent relationship.

> Fun fact: You can use the HasMany fieldtype anywhere - in Runway resources, entries, taxonomies, you name it!

### Configuration

It’s important that when configuring this fieldtype, the handle of the field is the same as the name of the `hasMany` relationship, eg: `authors`.

Also when configuring the fieldtype, you should choose the resource you wish to be available for selection by the user.

#### Additional configuration

-   **Eager Loading:** Using the `with` configuration option, you may specify any relationships you want to be eager loaded when the fieldtype is augmented.
-   **Title Format:** Using the `title_format` configuration option, you may specify a title format to be used when viewing related results in the CP. You should use Antlers in this setting. (eg. `{{ first_name }} {{ last_name }}`)
* **Reorderable** & **Order Column:** If you want to persist the ordering models, you may enable `reorderable` and specify the column used for storing the sort order (`order_column`).

### Table Mode

Runway's Has Many fieldtype includes a special 'Table mode'. Essentially, it lets you manage your related models but in a table, so you can see more information about each of the selected models.

![Table Mode](/img/runway/table-mode.png)

You may enable Table mode when configuring your 'Has Many' field.
