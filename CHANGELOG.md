# Changelog

## Unreleased

### What's fixed

* Fixed undefined method `uri()` exception when editing record.
* Now ignores the `.png` files when pulling down a release. (Probably not noticable)
* Only boots into the `DataRepository` if routing is enabled on at least one resource.

## v2.0.0 (2021-05-24)

Runway 2 introduces some minor breaking changes. Including a minimum requirement for Statamic 3.1 and the fact `models` are now called `resources` in the config (which our upgrade script should automate for you).

### What's new?

* [Front-end routing](https://github.com/doublethreedigital/runway/tree/2.0#routing)

### What's improved?

* Models are now 'Resources' - this will be reflected in your config file, it's essentially to stop you getting mixed up between a Runway Model and an Eloquent model
* Resources aren't just a big old arrays anymore 😅
* The Publish Forms in the CP are now Runway's (to allow for extra functionality)

## v2.0.0-beta.2 (2021-05-20)

### What's new

* Upgrade script to change `models` to `resources` in config
* Ability to disable Runway's migrations

## v2.0.0-beta.1 (2021-05-15)

Initial beta release for v2.0 - view [release notes for v2.0](https://github.com/doublethreedigital/runway/blob/2.0/CHANGELOG.md#v200-2021-xx-xx)
