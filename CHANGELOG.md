# Changelog

## Unreleased

## v2.4.3 (2022-07-23)

### What's new

- You may now specify the order & direction of models via the Runway config #160 #161

## v2.4.2 (2022-07-15)

### What's fixed

- Fixed an error when attempting to augment model that doesn't exist #156 #158
- Fixed an error when using the `{{ nav:breadcrumbs }}` tag on front-end routes #157 #159

## v2.4.1 (2022-07-06)

### What's fixed

- Fieldtypes: Formatted title will now also be returned from `toItemArray` method #155 by @edalzell

## v2.4.0 (2022-06-30)

### What's new

- You can now specify a `title_format` on Runway's fieldtypes #153 #154 by @edalzell

## v2.3.9 (2022-06-13)

### What's fixed

- If you had a `runwaySearch` scope on your model & tried to search using one of Runway's fieldtypes, Runway's default logic would still be used #149 #150 by @duncanmcclean

## v2.3.8 (2022-06-07)

### What's fixed

- Fixed an issue where columns with `json` cast would be 'double cast' when saved to the database #147 by @duncanmcclean

## v2.3.7 (2022-06-07)

### What's fixed

- Fixed a caching issue with fieldtype eager loading if the same model is augmented multiple times during a request #146 by @duncanmcclean

## v2.3.6 (2022-06-01)

### What's new

- You may now specify relationships to be eager loaded when a Runway field is augmented #145 by @duncanmcclean

## v2.3.5 (2022-04-28)

### What's fixed

- Fixed an issue with search on Runway fieldtypes - they'll no longer attempt to search hidden fields.

## v2.3.4 (2022-04-26)

### What's new

- You may now specify a `runwayListing` scope on your model to filter the results returned in the CP Listing Table #142 by @ryanmitchell

### What's improved

- There's now an option for toggling 'Create' button on BelongsTo fieldtype

### What's fixed

- Fixed an issue where search didn't work on fieldtypes

## v2.3.3 (2022-04-14)

### What's new

- Better customisation around CP Nav Items #141 by @duncanmcclean
- Resources can now be set to 'read-only' #139 by @duncanmcclean
- Added a new `runway:resources` command to show all registered resources #137 by @duncanmcclean

### What's improved

- Removed some old code (from before the fancy listing table) #140 by @duncanmcclean

## v2.3.2 (2022-04-13)

### What's fixed

- Fixed an issue loading resource results when you're using a fieldset in the related blueprint #136 by @Skullbock

## v2.3.1 (2022-04-08)

### What's improved

- Improved the performance of augmentation for Runway Fieldtypes (if you load the same record in multiple times) #135 by @ryanmitchell

## v2.3.0 (2022-02-26)

### What's new

- Statamic 3.3 is now supported! #120 by @duncanmcclean 🚀
- Related fields will now be automatically set when creating with the BelongsTo fieldtype #112 #124 by @duncanmcclean

### What's fixed

- Fixed permissions issues on the Listing Table actions & in the Control Panel Nav #119 by @jbfournot

### What's improved

- Eager loading magic has been refactored & is now backed up with some tests! #123 by @duncanmcclean

### Breaking changes

- Statamic 3.1 is no longer supported

Also, thanks to @SimonJnsson for a small documentation update!

## v2.2.5 (2022-02-15)

### What's fixed

- Another fix for the magic behind Runway's "relation guessing" code for eager loading #118 by @duncanmcclean

## v2.2.4 (2022-02-09)

### What's new

- You may now manually specify relations to be eager loaded, if you'd prefer to have complete control by @duncanmcclean

## v2.2.3 (2022-02-09)

### What's fixed

- The 'magic' behind the eager loading wasn't always resolving relation name's correctly by @duncanmcclean

## v2.2.2 (2022-02-08)

### What's fixed

- Fixed a couple of eager loading issues #116 #117 by @duncanmcclean

## v2.2.1 (2022-02-05)

### What's improved

- Added some eager loading to 'index queries' #113 #114 by @duncanmcclean and @DanielDarrenJones

## v2.2.0 (2022-01-29)

### What's new

- PHP 8.1 Support
- You may now add the 'Has Many' fieldtype to entries/taxonomies/globals #109 by @ryanmitchell
- The HasMany fieldtype now has an option in the Blueprints UI to toggle on/off resource creation #108 by @ryanmitchell

### Breaking changes

- Dropped support for Laravel 6 & Laravel 7. [You should upgrade to Laravel 8](https://laravel.com/docs/8.x/upgrade).

## v2.1.37 (2021-12-20)

### What's new

- Fixed issue viewing listing tables when using Eloquent users

## v2.1.36 (2021-12-13)

### What's new

- You can now use the Has Many fieldtype for 'Many to Many' relationships #102 by @psyao

## v2.1.35 (2021-11-24)

### What's fixed

- Fixed CP permission issues #101 by @edalzell

## v2.1.34 (2021-11-16)

### What's fixed

- Fixed issue when searching a resource with a 'Has Many' fieldtype #99 #100 by @edalzell

## v2.1.33 (2021-11-15)

### What's fixed

- Fixed 'Too few arguments' error with `ResponseCreated` event
- Fixed URIs being saved in a bad format if you don't start your route with `/` #98

## v2.1.32 (2021-10-30)

### What's fixed

- Fixed filenames of migrations generated by the `blueprint -> migration` tool #96

## v2.1.31 (2021-10-29)

### What's improved

- Enabled Bulk Actions on the Runway Listing Table #87

## v2.1.30 (2021-10-23)

### What's fixed

- Fixed a few round edges with permissions #94

## v2.1.29 (2021-10-22)

### What's improved

- Made some improvements to the Belongs To fieldtype, fixing an issue in the process #93 #91

## v2.1.28 (2021-10-20)

### What's fixed

- Fixed an issue viewing resources in the CP, where casting dates to `date_immutable` would cause issues. #89
- GraphQL queries now use the built-in `QueriesConditions` trait for filtering, not custom code

## v2.1.27 (2021-09-25)

### What's new

- 🎉 GraphQL API ([read the docs](https://runway.duncanmcclean.com/graphql)) #86 #54

## v2.1.26 (2021-09-24)

### What's new

- You can now use Eloquent Query Scopes with the Runway tag #82

## v2.1.25 (2021-09-24)

### What's new

- You can now eager load relationships using the `with` parameter on the Runway tag #84

## v2.1.24 (2021-09-20)

### What's new

- You can now generate blueprints from Eloquent models #58

## v2.1.23 (2021-09-18)

### What's new

- You can now create/edit models from Runway's relationship fieldtypes #76

## v2.1.22 (2021-09-14)

### What's fixed

- Fieldtype values are now flagged as invalid if model can't be found #78
- Fixed issue where 'Delete' action would not be available anywhere, apart from in Runway #79

## v2.1.21 (2021-09-09)

### What's new

- Added HasMany fieldtype #74

## v2.1.20 (2021-08-27)

### What's fixed

- Fixed issue with the post-select state of dropdown fieldtypess #67

## v2.1.19 (2021-08-20)

### What's fixed

- Fixed issues with 'primary columns' in the listing table

## v2.1.18 (2021-08-19)

**Why have you missed a couple of versions?** I tagged v2.1.7 as v2.1.17 by mistake and so to fix that, I'm tagging this as v2.1.18 which should make Packagist (and everywhere else) happy.

### What's new

- Support for [Statamic 3.2](https://statamic.com/blog/statamic-3.2-beta)

### What's fixed

- Fixed issue where the first field in a blueprint would be used as a primary column, where it should actually be a Relationship fieldtype.

## v2.1.10 (2021-08-12)

### What's fixed

- Fixed another bug affecting third-party packages (Laravel Nova this time)

## v2.1.9 (2021-08-11)

### What's fixed

- If you still have the `Responsable` interface on a model, you shouldn't get an error
- Fixed issue with old usage of Runway tag

## v2.1.8 (2021-08-11)

Re-tag of previous release, v2.1.7.

## v2.1.7 (2021-08-11)

### What's fixed

- Fixed an issue where the `Responsable` interface on models was causing issues (eg. with Inertia.js) #71

## v2.1.6 (2021-08-10)

### What's new

- Launched a [new documentation site](https://runway.duncanmcclean.com) for Runway! 🚀
- Added support for filters in the Listing Table #66
- For resources with multiple words in their handle, like `FooBar`, you can now reference them in Antlers with `{{ runway:foo_bar }}` #69
- You can now override the handle of a resource, just add `handle` to the resource's config
- You can now use a custom scope to handle Runway searches (useful for querying related models) #65

### What's fixed

- When a resource has no results, show a plural title

## v2.1.5 (2021-07-30)

### What's fixed

- Runway will now no longer 'double encode' JSON if you've added a cast to your model #62
- Fixed issue where updating models wouldn't work, if your model had a custom route key set

## v2.1.4 (2021-07-29)

### What's fixed

- Updated the way we handle dates on the edit resource page #60
- Runway will now throw the `ResourceNotFound` exception when a resource can not be found.
- Fixed a console error that popped up when configuring listing columns #61
- Little tiny other fix (probably didn't affect anyone - was related to something inside Runway's Vue components) #59

## v2.1.3 (2021-07-24)

### What's new

- You can now generate migrations from an existing blueprint #56

## v2.1.2 (2021-07-07)

### What's fixed

- You'll no longer get an error when editing a model if you have `getRouteKeyName` defined on your model. #53
- Fixed an issue where a fieldtype's `Index` component would not be rendered #52

## v2.1.1 (2021-07-06)

### What's fixed

- Listing rows will now be properly displayed with `preProcessIndex` (it'll fix the display of Date fields) #52

## v2.1.0 (2021-07-03)

**⚠️ This update contains breaking changes.**

### What's new

- A brand new Listing table for your models, just like the one used for entries #15
- You can now use real [Actions](https://statamic.dev/extending/actions#content), instead of 'Listing buttons'

### Breaking changes

**Listing Columns & Sorting**

The `listing.columns` and `listing.sort` configuration options have been removed. Columns and sorting are now configured from the listing table itself, in the same way it works for entries.

**Listing buttons**

This release removes the 'listing buttons' functionality, in place of Statamic's [Actions](https://statamic.dev/extending/actions#content) feature. Any listing buttons will no longer work. It's recommended you refactor into an Action during the upgrade process.

## v2.0.6 (2021-06-30)

### What's fixed

- Fixes issue with dirty state when creating model #41
- If it's a JSON field, make sure it's decoded before passing it to the publish form #40

## v2.0.5 (2021-06-16)

### What's fixed

- Fixed issues around 'primary key' stuff #39

## v2.0.4 (2021-06-04)

### What's fixed

- If there's no sidebar, we won't try and show one #38
- Fix an issue where the slug fieldtype failed to load #31
- Actually process fieldtypes in the resource listing #37

### What's improved

- Runway now has some defaults for 'Listing Columns' and 'Listing Sort', in case its not present in the config
- The Belongs To fieldtype will now give you a link to edit the related model

## v2.0.3 (2021-05-26)

### What's fixed

- White screen on publish form pages #36

## v2.0.2 (2021-05-25)

### What's fixed

- Another fix for the `dist/js/cp.js` error

## v2.0.1 (2021-05-25)

### What's fixed

- Fixed undefined method `uri()` exception when editing record.
- Now ignores the `.png` files when pulling down a release. (Probably not noticable)
- Only boots into the `DataRepository` if routing is enabled on at least one resource.
- _Hopefully_ fix the `Can't locate path <.../dist/js/cp.js>` error when installing.

## v2.0.0 (2021-05-24)

Runway 2 introduces some minor breaking changes. Including a minimum requirement for Statamic 3.1 and the fact `models` are now called `resources` in the config (which our upgrade script should automate for you).

### What's new?

- [Front-end routing](https://github.com/doublethreedigital/runway/tree/2.0#routing)

### What's improved?

- Models are now 'Resources' - this will be reflected in your config file, it's essentially to stop you getting mixed up between a Runway Model and an Eloquent model
- Resources aren't just a big old arrays anymore 😅
- The Publish Forms in the CP are now Runway's (to allow for extra functionality)

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

- Upgrade script to change `models` to `resources` in config
- Ability to disable Runway's migrations

## v2.0.0-beta.1 (2021-05-15)

Initial beta release for v2.0 - view [release notes for v2.0](https://github.com/doublethreedigital/runway/blob/2.0/CHANGELOG.md#v200-2021-xx-xx)
