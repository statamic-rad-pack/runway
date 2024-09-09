---
title: Search
---

As of Runway v3.0, you may now search Runway models using [Statamic's search functionality](https://statamic.dev/search#overview). It's as simple as adjusting your config file.

## Configuration

```php
// config/statamic/search.php
'myindex' => [
  'driver' => 'local',
  'searchables' => ['collection:blog', 'runway:order'],
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

### Working in sync with Laravel Scout and Algolia

In some use cases, you might already work with Laravel Scout and Algolia for some of your models. 

For example: You might have an E-shop which generates orders, and you use Laravel Scout in areas completely outside of the scope of Statamic (frontend or backend). But on the other hand, you do have a listing of those orders in Statamic just to make tiny changes or use Statamic Actions on some of those orders.

To keep your search index in sync, make sure to use the following code in your model:

```php
public function getScoutKey(): mixed
{
    return 'runway::order::' . $this->id;
}
```

Also make sure to have all the fields you want to be searchable in Statamic also in your "searchable array", for example like this:

```php
public function toSearchableArray()
{
    return [
        'id' => $this->id,
        'order_number' => $this->order_number,
        'customer_name' => $this->customer->full_name,
        'customer_email' => $this->customer->email,
        'article_string' => $this->articles->pluck('title')->implode(' '),
    ];
}
```

## Further documentation...

For further documentation on integrating Search in your site, please review the [Statamic Documentation](https://statamic.dev/search#overview).
