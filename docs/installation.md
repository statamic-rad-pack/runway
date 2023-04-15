---
title: 'Installation'
---

## System Requirements

-   PHP 7.4 & higher
-   Statamic 3.2 & higher
-   Laravel 8 & higher

## Installing via Composer (recommended)

First, you need to install Runway as a Composer dependency:

```
composer require doublethreedigital/runway
```

Next, publish the configuration file:

```
php artisan vendor:publish --tag="runway-config"
```

The configuration file will have been published as `config/runway.php`.

Now that everything's installed & published, you'll want to [configure Runway Resources](/resources) for each of the models you wish to be editable in Runway.

## Installing via the Control Panel

In the Control Panel, open the ‘Addons’ page, then search for ‘Runway’. Go into Runway’s addon page and hit ‘Install’.

![Runway in the Statamic CP](/img/runway/statamic-cp-addons-runway.png)

Next, open up your Terminal and run the following command which will publish the default configuration file.

```
php artisan vendor:publish --tag="runway-config"
```

The configuration file will now be located at `config/runway.php`.

Now that everything's installed & published, you'll want to [configure Runway Resources](/resources) for each of the models you wish to be editable in Runway.
