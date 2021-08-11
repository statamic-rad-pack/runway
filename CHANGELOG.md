# Changelog

## Unreleased

## v2.1.8 (2021-08-11)

Re-tag of previous release, v2.1.7.

## v2.1.7 (2021-08-11)

### What's fixed

* Fixed an issue where the `Responsable` interface on models was causing issues (eg. with Inertia.js) #71

## v2.1.6 (2021-08-10)

### What's new

* Launched a [new documentation site](https://runway.duncanmcclean.com) for Runway! 🚀
* Added support for filters in the Listing Table #66
* For resources with multiple words in their handle, like `FooBar`, you can now reference them in Antlers with `{{ runway:foo_bar }}` #69
* You can now override the handle of a resource, just add `handle` to the resource's config
* You can now use a custom scope to handle Runway searches (useful for querying related models) #65

### What's fixed

* When a resource has no results, show a plural title

## v2.1.5 (2021-07-30)

### What's fixed

* Runway will now no longer 'double encode' JSON if you've added a cast to your model #62
* Fixed issue where updating models wouldn't work, if your model had a custom route key set

## v2.1.4 (2021-07-29)

### What's fixed

* Updated the way we handle dates on the edit resource page #60
* Runway will now throw the `ResourceNotFound` exception when a resource can not be found.
* Fixed a console error that popped up when configuring listing columns #61
* Little tiny other fix (probably didn't affect anyone - was related to something inside Runway's Vue components) #59

## v2.1.3 (2021-07-24)

### What's new

* You can now generate migrations from an existing blueprint #56

## v2.1.2 (2021-07-07)

### What's fixed

* You'll no longer get an error when editing a model if you have `getRouteKeyName` defined on your model. #53
* Fixed an issue where a fieldtype's `Index` component would not be rendered #52

## v2.1.1 (2021-07-06)

### What's fixed

* Listing rows will now be properly displayed with `preProcessIndex` (it'll fix the display of Date fields) #52

## v2.1.0 (2021-07-03)

**⚠️ This update contains breaking changes.**

### What's new

* A brand new Listing table for your models, just like the one used for entries #15
* You can now use real [Actions](https://statamic.dev/extending/actions#content), instead of 'Listing buttons'

### Breaking changes

**Listing Columns & Sorting**

The `listing.columns` and `listing.sort` configuration options have been removed. Columns and sorting are now configured from the listing table itself, in the same way it works for entries.

**Listing buttons**

This release removes the 'listing buttons' functionality, in place of Statamic's [Actions](https://statamic.dev/extending/actions#content) feature. Any listing buttons will no longer work. It's recommended you refactor into an Action during the upgrade process.

## v2.0.6 (2021-06-30)

### What's fixed

* Fixes issue with dirty state when creating model #41
* If it's a JSON field, make sure it's decoded before passing it to the publish form #40

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
