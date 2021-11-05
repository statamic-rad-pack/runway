<!-- statamic:hide -->

![Banner](https://raw.githubusercontent.com/doublethreedigital/runway/2.1/banner.png)

## Runway

<!-- /statamic:hide -->

Runway gives you the option of displaying & managing your Eloquent models in Statamic sites.

### Control Panel integration

Runway fits right into the Control Panel - enabling you to create, edit and view your models. In most cases, you'll not notice the difference between an entry in the CP and an Eloquent model in the CP. 

* [Review documentation](https://runway.duncanmcclean.com/control-panel)

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

* [Review documentation](https://runway.duncanmcclean.com/front-end-routing)

### Antlers templating

In addition to front-end routing, you may also use Runway's tag to loop through your models and display the results. The tag supports filtering, using Eloquent scopes and sorting.

```antlers
{{ runway:products }}
    <h2>{{ name }}</h2>
    <p>Price: {{ price }}</p>
{{ /runway:products }}
```

* [Review documentation](https://runway.duncanmcclean.com/templating)

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

## Installation

First, require Runway as a Composer dependency:

```
composer require doublethreedigital/runway
```

Once installed, you’ll want to publish the default configuration file.

```
php artisan vendor:publish --tag="runway-config"
```

Now, [configure each of the 'resources'](https://runway.duncanmcclean.com/resources) you'd like to be available through Runway.

## Documentation

There's full documentation of Runway over on it's [documentation site](https://runway.duncanmcclean.com).

## Sponsor Duncan

This addon is open-source, meaning anyone can use this addon in their sites for **free**! 

However, maintaining and developing new features for open-source projects can take quite a bit of time. If you're using Runway in your production environment, please [consider sponsoring me](https://github.com/sponsors/duncanmcclean) (Duncan McClean) for a couple dollars a month.


## Security

Only the latest version of Runway (v2.1) will receive security updates if a vulnerability is found. 

If you discover a security vulnerability, please report it to Duncan straight away, [via email](mailto:security@doublethree.digital). Please don't report security issues through GitHub Issues.

<!-- statamic:hide -->

---

<p>
<a href="https://statamic.com"><img src="https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge" alt="Compatible with Statamic v3"></a>
<a href="https://packagist.org/packages/doublethreedigital/runway/stats"><img src="https://img.shields.io/packagist/v/doublethreedigital/runway?style=for-the-badge" alt="Runway on Packagist"></a>
<a href="https://tuple.app"><img src="https://img.shields.io/badge/Pairing%20with-Tuple-5A67D8?style=for-the-badge" alt="Pairing with Tuple"></a>
</p>

<!-- /statamic:hide -->
