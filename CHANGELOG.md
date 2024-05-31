# Changelog

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
