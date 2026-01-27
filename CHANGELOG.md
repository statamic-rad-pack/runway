# Changelog

## v9.0.0 (2026-01-27)

> Please review the [upgrade guide](https://runway.duncanmcclean.com/upgrade-guides/v8-to-v9) before upgrading.

### What's new
- Updated for Statamic 6 #658 by @duncanmcclean
- Live Preview support #721 by @duncanmcclean
- The Runway tag now uses the `runway` query scope #722 by @duncanmcclean
- Added `runway_resource` widget
- Update asset & term references in models #754 by @duncanmcclean

### What's changed
- Dropped support for PHP 8.2 and Laravel 10
- Runway now uses route-model binding for Control Panel routes #719 by @duncanmcclean
- `runway:rebuild-uri-cache` command no longer uses `withoutGlobalScopes` when querying resources #717 by @duncanmcclean



## v9.0.0-alpha.10 (2026-01-16)

### What's fixed
- Stop loading views from `resources/views` #771 by @duncanmcclean



## v9.0.0-alpha.9 (2026-01-12)

### What's fixed
- Use parent publish form values when populating BelongsTo relationship #767 by @duncanmcclean



## v9.0.0-alpha.8 (2026-01-09)

### What's fixed
- Sort out temporary JS imports #762 by @duncanmcclean
- Update revision history stack #763 by @duncanmcclean



## v9.0.0-alpha.7 (2026-01-05)

### What's new
- Update asset & term references in models #754 by @duncanmcclean

### What's fixed
- Avoid persisting active tab in URL hash when publish form is inline by @duncanmcclean
- Make the tests pass for now by @duncanmcclean
- Use class name to add content searchable by @duncanmcclean



## v9.0.0-alpha.6 (2025-12-04)

### What's improved
- Updated Runway searchable to go alongside changes in Statamic #748 by @duncanmcclean

### What's fixed
- Fixed issue with saving and errors state on publish forms

### What's breaking
- Dropped support for Laravel 11 #750 by @duncanmcclean



## v9.0.0-alpha.5 (2025-11-07)

### What's improved
- Converted widget to `VueComponent::render()` approach #735 by @duncanmcclean
- Moved listing into index page component #743 by @duncanmcclean
- Revision changes #744 by @duncanmcclean

### What's fixed
- Fix re-used state in publish forms #742 by @duncanmcclean

### What's breaking
- Renamed `scope` parameter on Runway tag #741 by @duncanmcclean



## v9.0.0-alpha.4 (2025-10-21)

### What's new
- All pages have been converted to Inertia #731 by @duncanmcclean

### What's fixed
- Corrected path to `@statamic/cms` npm package by @duncanmcclean



## v9.0.0-alpha.3 (2025-09-09)

### What's new
- Live Preview support #721 by @duncanmcclean
- The Runway tag now uses the `runway` query scope #722 by @duncanmcclean



## v9.0.0-alpha.2 (2025-09-01)

### What's changed
- Publish form optimisations #720 by @duncanmcclean
- Runway now uses route-model binding for Control Panel routes #719 by @duncanmcclean
- "Statamic Rad Pack" is now listed as the developer on the addon listing by @duncanmcclean
- Adopted the `#[Scope]` attribute for query scope examples in the docs #718 by @duncanmcclean
- `runway:rebuild-uri-cache` command no longer uses `withoutGlobalScopes` when querying resources #717 by @duncanmcclean



## v9.0.0-alpha.1 (2025-08-21)

> Please review the [upgrade guide](https://github.com/statamic-rad-pack/runway/blob/master/docs/upgrade-guides/v8-to-v9.md) before upgrading.

### What's new
- Updated for Statamic 6 #658 by @duncanmcclean
- Added `runway_resource` widget

### What's changed
- Dropped support for PHP 8.2 and Laravel 10
