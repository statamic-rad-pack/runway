# Changelog

## Unreleased

## v2.0.5 (2021-06-16)

### What's fixed

* Fixed issues around 'primary key' stuff #39

## v2.0.4 (2021-06-04)

### What's fixed

* If there's no sidebar, we won't try and show one #38
* Fix an issue where the slug fieldtype failed to load #31
* Actually process fieldtypes in the resource listing #37

### What's improved

* Runway now has some defaults for 'Listing Columns' and 'Listing Sort', in case its not present in the config
* The Belongs To fieldtype will now give you a link to edit the related model

## v2.0.3 (2021-05-26)

### What's fixed

* White screen on publish form pages #36

## v2.0.2 (2021-05-25)

### What's fixed

* Another fix for the `dist/js/cp.js` error

## v2.0.1 (2021-05-25)

### What's fixed

* Fixed undefined method `uri()` exception when editing record.
* Now ignores the `.png` files when pulling down a release. (Probably not noticable)
* Only boots into the `DataRepository` if routing is enabled on at least one resource.
* *Hopefully* fix the `Can't locate path <.../dist/js/cp.js>` error when installing.

## v2.0.0 (2021-05-24)

Runway 2 introduces some minor breaking changes. Including a minimum requirement for Statamic 3.1 and the fact `models` are now called `resources` in the config (which our upgrade script should automate for you).

### What's new?

* [Front-end routing](https://github.com/doublethreedigital/runway/tree/2.0#routing)

### What's improved?

* Models are now 'Resources' - this will be reflected in your config file, it's essentially to stop you getting mixed up between a Runway Model and an Eloquent model
* Resources aren't just a big old arrays anymore 😅
* The Publish Forms in the CP are now Runway's (to allow for extra functionality)

### Upgrade Guide

Upgrading to v2.0 is reasonably simple. In your `composer.json` file, update the `doublethreedigital/runway` version constraint:

```json
"doublethreedigital/runway": "2.0.*"
```

Then run:

```
composer update doublethreedigital/runway --with-dependencies
```

Because of the magic of Statamic's new [Upgrade Scripts](https://statamic.dev/upgrade-guide-3-0-to-3-1#update-scripts), all config changes will be automatically made.

## v2.0.0-beta.2 (2021-05-20)

### What's new

* Upgrade script to change `models` to `resources` in config
* Ability to disable Runway's migrations

## v2.0.0-beta.1 (2021-05-15)

Initial beta release for v2.0 - view [release notes for v2.0](https://github.com/doublethreedigital/runway/blob/2.0/CHANGELOG.md#v200-2021-xx-xx)
