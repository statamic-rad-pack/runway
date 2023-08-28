<!-- statamic:hide -->

<p align="center">
<picture>
    <source srcset="./logo-dark.svg" media="(prefers-color-scheme: dark)">
    <img align="center" width="250" height="70" src="./logo-default.svg">
</picture>
</p>
<br>

<!-- /statamic:hide -->

Runway gives you the ability to display & manage your Eloquent models in Statamic.

> "With Runway I was able to quickly make a beautiful admin panel that administrators can use. If Runway didn't exist, it would have taken me a week or two to build something that wasn't quite as nice. Thanks for saving me days and days of work Duncan!"
>
> **[Erin Dalzell, Certified Statamic Partner](https://silentz.co)**

[Read the docs](https://runway.duncanmcclean.com).

## Features

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

## Support

If you find a bug, have some question or have a feature request, please open a [GitHub Issue or Discussion](https://github.com/duncanmcclean/runway/issues/new/choose).

> Please note: only the latest version of this addon is supported. Any bug reports regarding an old version will be closed.

<!-- statamic:hide -->

## Contributing

Contributions are welcome, and are accepted via pull requests. You should follow this process when contributing:

1. Fork the repository
2. Make your code change
3. Open a pull request, detailing the changes you've made.

If your pull request is a Work in Progress, please mark your pull request as a draft.

## Security

If you've found a bug regarding security please email security@doublethree.digital instead of using the issue tracker.

<!-- /statamic:hide -->
