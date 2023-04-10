---
title: 'Installation'
---

## System Requirements

-   PHP 7.4 & higher
-   Statamic 3.2 & higher
-   Laravel 8 & higher

## Installing via Composer

First, run this command which will require Runway as a dependency of your app.

```
composer require doublethreedigital/runway
```

Once installed, you’ll want to publish the default configuration file.

```
php artisan vendor:publish --tag="runway-config"
```

The configuration file will now be located at `config/runway.php`. You may now customise the config file to add your resources.
