---
title: Search
---

As of Runway v3.0, you may now search Runway models using [Statamic's search functionality](https://statamic.dev/search#overview). It's as simple as adjusting your config file.

## Configuration

```php
// config/statamic/search.php
'myindex' => [
  'driver' => 'local',
  'searchables' => ['collection:blog', 'runway:products'],
]
```

You can scope the resources you'd like to be searchable, using the `runway:{resourceHandle}` syntax. If you'd like everything in Runway to be searchable, you can use`runway:*`.

### Title field

By default, when displaying search results, Runway will use the first listable column as the 'title field'. If you'd like to change this, add the `title_field` option to your resource's config:

```php
'resources' => [
	\App\Models\Order::class => [
	    'name' => 'Orders',
		'title_field' => 'name',
	],
],
```

## Further documentation...

For further documentation on integrating Search in your site, please review the [Statamic Documentation](https://statamic.dev/search#overview).
