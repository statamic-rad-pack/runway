---
title: 'Installation'
---

## System Requirements

* Statamic 3.1
* PHP 7.4 / PHP 8


## Installing via Composer (recommended)

First, run this command which will require Runway as a dependency of your app.

```
composer require doublethreedigital/runway
```

Once installed, you’ll want to publish the default configuration file.

```
php artisan vendor:publish --tag="runway-config"
```

The configuration file will now be located at `config/runway.php`. You may now customise the config file to add your resources.


## Installing via the Control Panel

In the Control Panel, open the ‘Addons’ page, then search for ‘Runway’. Go into Runway’s addon page and hit ‘Install’.


![Runway in the Statamic CP](/assets/statamic-cp-addons-runway.png)

Next, open up your Terminal and run the following command which will publish the default configuration file.

```
php artisan vendor:publish --tag="runway-config"
```

The configuration file will now be located at `config/runway.php`. You may now customise the config file to add your resources.
