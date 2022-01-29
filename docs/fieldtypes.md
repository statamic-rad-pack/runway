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

### Configuration

It’s important that when configuring this fieldtype, the handle of the field is the same as the name of the `hasMany` relationship, eg: `authors`.

Also when configuring the fieldtype, you may choose the resource you wish to be available for selection by the user.
