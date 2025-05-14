# Changelog

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
