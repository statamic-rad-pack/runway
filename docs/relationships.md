---
title: Relationships
---

Relationships are an important part of any web application. Most of your models will be related in some way to other models. Runway provides a way to manage those relationships within Statamic.

## Belongs To

Runway provides a dedicated fieldtype to manage [`belongsTo`](https://laravel.com/docs/master/eloquent-relationships#one-to-many-inverse) relationships within Statamic. 

```php
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class Post extends Model
{
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
```

```yaml
-
  handle: author_id
  field:
    type: belongs_to
    display: Author
    resource: author
```

You should make sure that the field handle matches the column name in the database.

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

Runway provides a dedicated fieldtype to manage [`hasMany`](https://laravel.com/docs/master/eloquent-relationships#one-to-many) relationships within Statamic.

```php
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class Author extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

```yaml
-
  handle: posts
  field:
    type: has_many
    display: Posts
    resource: post
```

You should ensure that the field handle matches the name of the Eloquent relationship in your model (the method name).

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

## Belongs To Many

The Has Many fieldtype is also compatible with [`belongsToMany`](https://laravel.com/docs/master/eloquent-relationships#many-to-many) relationships. You can use the Has Many fieldtype on both sides of the relationship.

```php
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
 
class Category extends Model
{
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
```

```yaml
-
  handle: posts
  field:
    type: has_many
    display: Posts
    resource: post
```

You should ensure that the field handle matches the name of the Eloquent relationship in your model (the method name).

For more information on templating with the Has Many fieldtype and the config options available, see the [Has Many](#has-many) section.

## Polymorphic Relationships

Runway doesn't currently support Polymorphic relationships out of the box, since they can get pretty complicated. If you need it, please [upvote this feature request](https://github.com/statamic-rad-pack/runway/discussions/245).
