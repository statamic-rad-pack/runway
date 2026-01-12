# Changelog

## v8.8.4 (2026-01-12)

### What's improved
- Ability to prevent eager loading by providing empty `with` array #761 by @claasjansen
- Update search indexes in a queue #764 by @janis-ps

### What's fixed
- Fixed error when visiting URL of soft-deleted model #766 by @duncanmcclean
- Fixed `unique` validation rule #765 by @duncanmcclean
- Render `ResourceNotFound` exception as a 404 #755 by @BobWez98



## v8.8.3 (2025-12-12)

### What's fixed
- Fix runway search on models with individual connections #753 by @godismyjudge95
- PHP 8.5 compatibility #752 by @duncanmcclean



## v8.8.2 (2025-11-24)

### What's fixed
- Fixed BelongsToMany field not saving #751 by @janis-ps



## v8.8.1 (2025-11-24)

### What's improved
- Added `RoutingModel::model()` method by @duncanmcclean



## v8.8.0 (2025-11-24)

### What's new
- Allow saving custom relationship types #747 by @caseydwyer



## v8.7.2 (2025-11-17)

### What's fixed
- Handle enums correctly in listings #746 by @janis-ps



## v8.7.1 (2025-11-05)

### What's fixed
* Fixed augmentation on runway routes #745 by @duncanmcclean



## v8.7.0 (2025-11-04)

### What's new
* Added `default_publish_state` config option #736 by @duncanmcclean

### What's fixed
* Publish/unpublish actions should check `publish {resource}` permission #737 by @duncanmcclean
* Fix error when using nocache tag on Runway route #738 by @duncanmcclean



## v8.6.6 (2025-10-28)

### What's fixed
* Use augmented values to make the title #734 by @mmodler



## v8.6.5 (2025-10-21)

### What's fixed
* Fixed `published() on null` error if model is missing #733 by @joshtaylordev



## v8.6.4 (2025-09-03)

### What's fixed
* Avoid saving revealer fields #727 #729 by @duncanmcclean
* Fix sorting by nested fields #716 #728 by @duncanmcclean



## v8.6.3 (2025-08-25)

### What's fixed
* Fix `runway:import-collection` command on Ubuntu #715 by @amadeann



## v8.6.2 (2025-08-21)

### What's fixed
* Added column existence check to `runwaySearch` query scope #714 by @kevinmeijer97



## v8.6.1 (2025-08-14)

### What's fixed
* Fixed inline actions on edit view #710 #711 by @persteinhorst



## v8.6.0 (2025-08-11)

### What's new
* Allow programmatic registration of resources #707 by @Pluuk
* Update RoutingModel `model` property to be publicly accessible #706 by @jamesmpigott

### What's fixed
* Fix error when searching via fieldtype #699 #709 by @duncanmcclean
* Refactor `BaseFieldtype::getIndexItems` #708 by @duncanmcclean
* Delete `php-cs-fixer.phar` #704 by @duncanmcclean



## v8.5.2 (2025-07-10)

### What's fixed
* Fix issues when filtering, sorting and searching nested fields #700 #702 by @duncanmcclean
* Improve nested field augmentation #698 #701 by @duncanmcclean



## v8.5.1 (2025-06-11)

### What's fixed
* Collection Importer fixes #696 by @duncanmcclean
* Prevent errors from PostgreSQL when querying `runway_uris` table #689 #695 by @duncanmcclean
* `RunwayRoutes` trait check should consider traits on parent classes #691 #694 by @duncanmcclean
* Use `runway` query scope when fetching related models #692 #693 by @duncanmcclean



## v8.5.0 (2025-05-22)

### What's new
* Added `where_in` parameter to Runway tag #688 by @duncanmcclean
* Documented usage with Antlers Blade Components by @duncanmcclean



## v8.4.0 (2025-05-14)

### What's new
* Require `spatie/laravel-ray` in dev #687 by @duncanmcclean

### What's fixed
* Fixed error when serializing resources with appended attributes #686 by @duncanmcclean
* Fixed search on listing table with nested fields #681 by @duncanmcclean



## v8.3.1 (2025-03-31)

### What's fixed
* `getRouteKeyName` method will only be added to models when imported collection uses slugs #678 by @duncanmcclean
* Fixed typo in `resources.md` #674 by @mefenlon



## v8.3.0 (2025-03-20)

### What's new
* Added `query_scopes` option to fieldtypes #672 by @duncanmcclean
* Added `runway` query scope #671 by @duncanmcclean



## v8.2.0 (2025-02-27)

### What's new
* Laravel 12 support #664 by @duncanmcclean



## v8.1.2 (2025-02-21)

### What's fixed
* Reverted "Fix attributes returning a collection instead of an array" #666 by @duncanmcclean



## v8.1.1 (2025-02-21)

### What's fixed
* Fixed missing "Status" filter on collections #663 by @duncanmcclean



## v8.1.0 (2025-02-20)

### What's new
* The `rebuild-uris` command now bypasses global scopes #661 by @godismyjudge95



## v8.0.1 (2025-02-17)

### What's fixed
* Improve compatibility with per model database connections #659 by @godismyjudge95
* Fix attributes returning a collection instead of an array #654 by @kailumworkhouse



## v8.0.0 (2025-02-07)

### Read First 👀
Be sure to read the [Upgrade Guide](https://runway.duncanmcclean.com/upgrade-guides/v7-to-v8) first as manual changes may be necessary.

### What's new
* Collection Importer #653 by @duncanmcclean

### What's changed
* Changed how nested fields work #568 by @duncanmcclean
* Removed the `cp_icon` config in favour of the CP Nav Customizer #652 by @duncanmcclean
* Updated the `runway_uris` migration stub to support UUIDs #656 by @duncanmcclean
