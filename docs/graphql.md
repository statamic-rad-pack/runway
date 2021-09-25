---
title: GraphQL
---

GraphQL is an awesome way to fetch just the right information you need from your backend. It's commonly used in 'headless' environments.

Statamic includes a read-only [GraphQL API](https://statamic.dev/graphql) out of the box. Runway extends upon this so you can query your Eloquent models.

## Enabling for resources

GraphQL must be enabled for each of the resources you wish to query. It's as simple as adding to your config:

```php
// config/runway.php

'resources' => [
    \App\Models\Product::class => [
        'name' => 'Products',
        'blueprint' => 'product',

        'graphql' => true,
    ],
],
```

You must also ensure you have [GraphQL enabled](https://statamic.dev/graphql#enable-graphql) in Statamic as well for it to be available to you.

> As a side note, please ensure you've installed the `doctrine/dbal` package, Runway uses it for reviewing your database columns. To install it, run `composer require doctrine/dbal`

## Queries

For each resource, there's two kinds of queries you can do. An 'index' query and a 'show' query:

### Index Query

Example of an index query:

```graphql
{
    products {
        data {
            id
            name
            price
            description
        }
    }
}
```

An index query also allows for pagination between results, you can read up more on that in the [Statamic Documentation](https://statamic.dev/graphql#pagination).

### Show Query

Example of a show query:

```graphql
{
    products(id: "2") {
        id
        name
        price
        description
    }
}
```

## Relationships

If you're using the 'Belongs To' or 'Has Many' fieldtypes provided by Runway, you can also query the related models.

```graphql
{
  product(id: "2") {
    id
    name
    brand {
      id
      name
      created_at
      updated_at
    }
  }
}
```

Notice that in the above example, we have a 'Belongs To' fieldtype which we're querying as simply `brand`, instead of `brand_id`. Runway removes the `_id` for you so you can build a nice, clean query.

## Filtering and Sorting

You may also filter & sort results the same way you would with the built-in queries. [Review the Statamic Docs](https://statamic.dev/graphql#filtering).
