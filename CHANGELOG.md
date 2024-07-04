# Changelog

## v7.5.3 (2024-07-04)

### What's fixed
* Prevent redirect when creating model via inline publish form #548 by @duncanmcclean
* Fix error when saving models via inline publish form #547 by @duncanmcclean



## v7.5.2 (2024-07-01)

### What's fixed
* Fixed nested fields being saved incorrectly in revision attributes #541 #543 by @edalzell
* Fixed save button label when revisions are enabled #540 #542 by @edalzell
* Fixed bulk actions on Asset & User listing tables #530 #535 #537 by @edalzell
* Fixed PSR-4 autoloading issues in the test suite #538 #545 by @duncanmcclean
* Prevented the publish form page titles from being localized #531 by @duncanmcclean
* The "Revisions" panel on the publish form now mentions "Model" instead of "Entry" #536 by @duncanmcclean



## v7.5.1 (2024-06-21)

### What's fixed
* Fixed Ignition Runnable Solutions #529 by @duncanmcclean



## v7.5.0 (2024-06-17)

### What's new
* Added `search_index` config option to resources #524 by @ryanmitchell

### What's fixed
* Fixed "title field" not being used when columns have been customized #526 by @duncanmcclean



## v7.4.0 (2024-06-17)

### What's new
* Added support for `int` and `timestamp` columns in `runway:generate-blueprints` command #523 #525 by @ryanmitchell



## v7.3.1 (2024-06-06)

### What's fixed
* Fixed broken Has Many fieldtype when used outside a Runway context #517 by @duncanmcclean



## v7.3.0 (2024-06-06)

### What's new
* You can now hide the "Create" button by setting `hide: true` on blueprints #508 by @ryanmitchell

### What's fixed
* Fixed issue where the sidebar section would show on the Publish Form when it's not needed #515 by @duncanmcclean



## v7.2.0 (2024-06-04)

### What's new
* Revisions support #492 by @edalzell
* Runway now supports "publish states" (publishing/unpublishing models) #506 by @duncanmcclean

### What's improved
* Improved page titles on publish form pages #513 by @duncanmcclean
* Updated publish form to better align with Statamic's entry publish form #509 by @duncanmcclean

### What's fixed
* Fixed errors when getting redirect URL for models using frontend routing #510 by @duncanmcclean



## v7.1.1 (2024-05-31)

### What's fixed
* Fixed styling issues in Dark Mode #504 #505 by @mynetx



## v7.1.0 (2024-05-20)

### What's new
* Ability to run actions from publish forms #498 by @duncanmcclean
* Runway will now handle showing/updating role & user group fields on User models #485 #500 by @duncanmcclean
* Added config option for customizing the name of the `runway_uris` table #490 #499 by @duncanmcclean
* Improve exception handling #497 by @duncanmcclean

### What's fixed
* Fixed empty element showing border at the bottom of the publish form page by @duncanmcclean



## v7.0.0 (2024-05-09)

### Read First 👀
Be sure to read the [Upgrade Guide](https://runway.duncanmcclean.com/upgrade-guides/v6-to-v7) first as manual changes may be necessary.

### What's new

* Statamic 5 support #441 by @duncanmcclean

### What's changed

* Dropped PHP 8.1 support
* Dropped Statamic 4 support
* Resource handles are now generated differently #480 by @duncanmcclean

### What's improved

* Improved the output of Runway's commands
* Replaced `doctrine/dbal` dependency in favour of Laravel's new built-in methods #468 by @duncanmcclean
* Augmentation improvements #481 by @duncanmcclean
