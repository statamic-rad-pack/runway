---
title: 'Installation'
---

## System Requirements

-   PHP 7.4 & higher
-   Statamic 3.2 & higher
-   Laravel 8 & higher

## Installing via Composer

First, you need to install Runway as a Composer dependency:

```
composer require statamic-rad-pack/runway
```

Next, publish the configuration file:

```
php artisan vendor:publish --tag="runway-config"
```

The configuration file will have been published as `config/runway.php`.

Now that everything's installed & published, you'll want to [configure Runway Resources](/resources) for each of the models you wish to be editable in Runway.
