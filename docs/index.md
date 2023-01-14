---
title: Introduction
table_of_contents: false
---

Runway is a Statamic addon that lets you manage your Eloquent models in Statamic, as if they were entries in a collection.

<div class="testimonial">
    <div class="testimonial-body">
        “With Runway I was able to quickly make a beautiful admin panel that administrators can use. If Runway didn't exist, it would have taken me a week or two to build something that wasn't quite as nice. Thanks for saving me days and days of work Duncan!”
    </div>
    <div class="testimonial-person">
        <a href="https://silentz.co" target="_blank">
            <img src="/img/testimonials/erin.jpeg">
            <span>Erin Dalzell, Certified Statamic Partner</span>
        </a>
    </div>
</div>

### Control Panel integration

Runway fits right into the Control Panel - enabling you to create, edit and view your models. In most cases, you'll not notice the difference between an entry in the CP and an Eloquent model in the CP.

-   [Review documentation](https://runway.duncanmcclean.com/control-panel)

### Front-end routing

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

### Antlers templating

In addition to front-end routing, you may also use Runway's tag to loop through your models and display the results. The tag supports filtering, using Eloquent scopes and sorting.

```antlers
{{ runway:products }}
    <h2>{{ name }}</h2>
    <p>Price: {{ price }}</p>
{{ /runway:products }}
```

-   [Review documentation](https://runway.duncanmcclean.com/templating)

### GraphQL API

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
