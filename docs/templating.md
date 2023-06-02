---
title: Templating
---

There’s no point storing data in a database for it never to be used, is there?

## Tags

Runway provides a `{{ runway }}` Antlers tag to enable you to get model data for your resources.

For example, if you wish to create a listing/index page for a resource, you can use the tag like shown below:

```antlers
{{ runway:post }}
	<h2>{{ title }}</h2>
	<p>{{ intro_text }}</p>
{{ /runway:post }}
```

There’s a bunch of helpful parameters you can use on the tag as well…

### Sorting

You may use the `sort` parameter to adjust the order of the results.

```antlers
{{ runway:post sort="title:asc" }}
	<h2>{{ title }}</h2>
	<p>{{ intro_text }}</p>
{{ /runway:post }}
```

### Eloquent Scopes

If you've defined a scope on your Eloquent model and you want to filter by that in your front-end you may use the `scope` parameter.

```php
// app/Models/Post.php

public function scopeFood($query)
{
    $query->whereIn('title', ['Pasta', 'Apple', 'Burger']);
}
```

```antlers
{{ runway:post scope="food" }}
	<h2>{{ title }}</h2>
	<p>{{ intro_text }}</p>
{{ /runway:post }}
```

If you need to you can provide arguments to the scope like so:

```antlers
{{ runway:post scope="food:argument" }}
```

In the above example, `argument` can either be a string or we'll grab it from 'the context' (the available variables) if we can find it.

You may also provide multiple scopes, if that's something you need...

```antlers
{{ runway:post scope="food:argument|fastfood" }}
```

### Filtering

Just like with the collection tag, you may filter your results like so:

```antlers
{{ runway:post where="author_id:duncan" }}
	<h2>{{ title }}</h2>
	<p>{{ intro_text }}</p>
{{ /runway:post }}
```

You can also query Belongs To / Has Many fields using the `where` parameter. Simply provide the ID(s) of the related models.

```antlers
{{ runway:post where="categories:2" }}
	<h2>{{ title }}</h2>
	<p>{{ intro_text }}</p>
{{ /runway:post }}
```

### Eager Loading

If your model has a relationship that you'd like to bring into the template, you may specify the `with` parameter.

```antlers
{{ runway:post with="user" }}
	<h2>{{ title }}</h2>
	<p>By {{ user:name }}</p>
{{ /runway:post }}
```

You can specify multiple relationships to eager load, just separate with pipes.

```antlers
{{ runway:post with="user|comments" }}
```

:::tip Hot Tip
Eager Loading can make a **massive** difference in the speed of your queries.
:::

### Limiting

If you only want X number of results returned instead of ALL of them, you may specify a `limit`.

```antlers
{{ runway:post limit="15" }}
	<h2>{{ title }}</h2>
	<p>{{ intro_text }}</p>
{{ /runway:post }}
```

### Scoping

As [with the collection tag](https://statamic.dev/tags/collection#scope), you may use the `as` parameter to scope your results.

```antlers
{{ runway:post as="posts" }}
	{{ posts }}
		<h2>{{ title }}</h2>
		<p>{{ intro_text }}</p>
	{{ /posts }}
{{ /runway:post }}
```

### Pagination

If you want to paginate your results onto multiple pages, you can use the `paginate` parameter, along with [scoping](#scoping) and [limiting](#limiting).

Using the Runway tag with pagination is a little more complicated but it’s not rocket science.

```antlers
{{ runway:post as="posts" paginate="true" limit="10" }}
    {{ if no_results }}
        <p>Nothing has been posted yet. Sad times.</p>
    {{ /if }}

    {{ posts }}
        <h2>{{ title }}</h2>
	<p>{{ intro_text }}</p>
    {{ /posts }}

    {{ paginate }}
        {{ if prev_page }}
            <a href="{{ prev_page }}">Previous</a>
        {{ /if }}

        <span>Page {{ current_page }} of {{ total_pages }}</span>

        {{ if next_page }}
            <a href="{{ next_page }}">Next</a>
        {{ /if }}
    {{ /paginate }}
{{ /runway:post }}
```

## Augmentation

All the results output from the Runway tag are ‘augmented’, which essentially means everything is the same as you’d expect if you had the same data in an entry.

The process of augmentation takes a value (from the database in our case) and processes it via the fieldtype into a value appropriate for the front-end.

Let’s imagine you have an Assets field in your blueprint, you may have something selected for the field. In the database, the path to the asset is stored.

When this field is then augmented, Statamic does a lookup of the asset by it’s path and spits out variables like `url`, `alt` and a couple of others.

(There’s another explanation of augmentation over on [the Statamic documentation](https://statamic.dev/extending/augmentation#what-is-augmentation))
