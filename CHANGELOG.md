# Changelog

## v7.13.0 (2024-11-29)

### What's new
* PHP 8.4 Support #633 by @duncanmcclean

### What's improved
* Docs: Updated settings title in fieldtypes table #636 by @Joel-Jensen



## v7.12.0 (2024-11-04)

### What's improved
* Updated the `.gitattributes` file by @duncanmcclean

### What's fixed
* Fixed an issue where Has Many relationships weren't being resolved #624 #630 by @duncanmcclean
* Fixed an error when augmenting model mutators #627 #629 by @TheBnl
* Added a check when getting a search value to see if the model has a method #625 #628 by @ryanmitchell
* Fixed deprecation error when checking if a value is a JSON string #626 by @indykoning
* Fixed the incorrect context for resource actions on the listing page by @duncanmcclean



## v7.11.0 (2024-10-11)

### What's new
* Runway URIs are now warmed as part of the `static:warm` command #623 by @duncanmcclean

### What's improved
* Prevented duplicate queries for table columns by @duncanmcclean
* Minor optimisations around the `isJson` function #619 by @indykoning
* Tidied up the `ServiceProvider` to take advantage of autoloading #622 by @duncanmcclean

### What's fixed
* Fixed an issue where the "Published" toggle was disabled for non-super users #621 by @duncanmcclean



## v7.10.1 (2024-09-27)

### What's fixed
* Fixed missing title on fieldtype dropdown options #615 #617 by @duncanmcclean
* Fixed error when linking related model with publish states #612 #616 by @duncanmcclean
* Bump minimum version of `statamic/cms` to 5.26.0, to hopefully fix an issue with actions by @duncanmcclean



## v7.10.0 (2024-09-25)

### What's improved
* Improve feedback when action fails #608 by @duncanmcclean



## v7.9.5 (2024-09-24)

### What's fixed
* Fixed augmentation on frontend routes #606 #610 by @duncanmcclean
* Fixed error when removing filters #607 #609 by @duncanmcclean



## v7.9.4 (2024-09-19)

### What's fixed
* Fix issue in the REST API when using plurals as resource handles #605 by @duncanmcclean



## v7.9.3 (2024-09-17)

### What's fixed
* Fixed an error with the Has Many fieldtype when used on entries #600 #601 by @duncanmcclean



## v7.9.2 (2024-09-16)

### What's fixed
* Fixed an error that occurred when creating a model with a Has Many relationship #598 #599 by @BobWez98



## v7.9.1 (2024-09-12)

### What's fixed
* Add missing `page` variable for Blade templates #584 #590 by @duncanmcclean



## v7.9.0 (2024-09-05)

### What's new
* Improvements around unlinking relationships #582 #595 by @duncanmcclean
* When adding a new model, Runway will automatically create a blueprint for you, based on the database columns #593 by @duncanmcclean

### What's fixed
* Fixed sorting in listing tables #587 #591 by @duncanmcclean
* Fixed an issue where relationship fields weren't showing in the fieldtype selector #588 #592 by @duncanmcclean

### What's removed
* Removed the `runway:generate-blueprints` command #594 by @duncanmcclean



## v7.8.0 (2024-08-14)

### What's new
* Added `{{ runway:count }}` tag #583 by @vJoeyz
* Resource Actions #564 by @edalzell

### What's improved
* Tidied up Vue components #585 by @duncanmcclean



## v7.7.5 (2024-08-09)

### What's fixed
* Prevent creating blueprint files when running in the console #581 by @BobWez98



## v7.7.4 (2024-08-06)

### What's fixed
* Fixed nested JSON fields when using revisions #577 by @edalzell
* Non-text fields shouldn't be used for resource "title fields" anymore #578 #579 by @duncanmcclean



## v7.7.3 (2024-07-29)

### What's fixed
* Fix protected model accessors & model mutators without accessor equivelent #576 by @duncanmcclean
* Prevented saving of computed relationship fields #575 by @duncanmcclean
* Fixed explicit `relationship_name` being overwritten in Has Many relationships #574 by @manogi



## v7.7.2 (2024-07-24)

### What's fixed
* Added missing methods to Runway's `HasAugmentedInstance` trait #567 by @simonworkhouse
* Fixed issues when the field handle isn't the same as the relationship name #570 by @duncanmcclean



## v7.7.1 (2024-07-19)

### What's fixed
* Fixed error when accessing Runway's fieldtypes via GraphQL #561 #565 by @duncanmcclean



## v7.7.0 (2024-07-18)

### What's new
* Added `augmented` hook #560 by @ryanmitchell



## v7.6.0 (2024-07-12)

### What's new
* Added icons to Runway's fieldtypes #558 by @duncanmcclean
* Added "Publish" permission for resources with publish states enabled #553 by @edalzell

### What's fixed
* Refactored the Has Many fieldtype #556 by @duncanmcclean
* Fixed incorrect revisions status #555 by @duncanmcclean
* Move test suite from metadata to attributes #557 by @duncanmcclean
* Fixed dirty state after entry action or revision publish #559 by @duncanmcclean
* An exception is now thrown, instead of an infinite loop, when a published column is missing #554 by @duncanmcclean



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
