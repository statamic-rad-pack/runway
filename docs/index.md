---
title: Introduction
table_of_contents: false
---

## Control Panel integration

Runway fits right into the Control Panel - enabling you to create, edit and view your models. In most cases, you'll not notice the difference between an entry in the CP and an Eloquent model in the CP.

-   [Review documentation](https://runway.duncanmcclean.com/control-panel)

## Front-end routing

Need to show your models on the front-end of your site? No problem - Runway's got that under control. Simply tell Runway the route you'd like to use and it'll serve up the front-end for you.

```php
// config/runway.php

return [
    'resources' => [
        \App\Models\Product::class => [
            'route' => '/products/{{ slug }}',
        ],
    ],
];
```

-   [Review documentation](https://runway.duncanmcclean.com/frontend-routing)

## Antlers templating

In addition to front-end routing, you may also use Runway's tag to loop through your models and display the results. The tag supports filtering, using Eloquent scopes and sorting.

```antlers
{{ runway:product }}
    <h2>{{ name }}</h2>
    <p>Price: {{ price }}</p>
{{ /runway:product }}
```

-   [Review documentation](https://runway.duncanmcclean.com/templating)

## GraphQL API

If you're a GraphQL fan, you're now able to fetch your models via GraphQL. Runway will augment the fields just like you'd expect.

```graphql
{
    products(limit: 25, sort: "name") {
        data {
            id
            name
            price
            description
        }
    }
}
```

### REST API

If you don't like GraphQL and would prefer plain old REST, you can do that too. Runway easily integrates with Statamic's REST API.

```php
'resources' => [
    'collections' => true,
    // ...
    'runway' => [
        'product' => true,
    ],
],
```

### Search

Runway integrates with Statamic's [Search](https://statamic.dev/search) feature, allowing you to search your Eloquent models in the Control Panel and via the `{{ search:results }}` tag. It's just as simple as adjusting your config file.

```php
// config/statamic/search.php

'indexes' => [
    'myindex' => [
        'driver' => 'local',
        'searchables' => ['collection:blog', 'runway:order'],
    ],
],
```

<div class="not-prose testimonial">
    <div class="testimonial-body">
        “With Runway I was able to quickly make a beautiful admin panel that administrators can use. If Runway didn't exist, it would have taken me a week or two to build something that wasn't quite as nice. Thanks for saving me days and days of work Duncan!”
    </div>
    <div class="testimonial-person">
        <a href="https://silentz.co" target="_blank">
            <img src="/img/testimonials/erin.jpeg">
            <div>
                <p>Erin Dalzell</p>
                <span>Certified Statamic Partner</span>
            </div>
        </a>
    </div>
</div>
