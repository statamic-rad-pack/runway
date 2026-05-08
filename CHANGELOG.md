# Changelog

## v9.4.5 (2026-05-08)

### What's fixed
- Publish form tweaks [#811](https://github.com/statamic-rad-pack/runway/pull/811) by @duncanmcclean
- Fix toggle field filter [#812](https://github.com/statamic-rad-pack/runway/pull/812) by @duncanmcclean



## v9.4.4 (2026-04-17)

### What's fixed
- Fix updated_at timestamp not updating when field is read-only [#807](https://github.com/statamic-rad-pack/runway/pull/807) by @duncanmcclean



## v9.4.3 (2026-04-13)

### What's fixed
- Qualify the primary key in queries [#806](https://github.com/statamic-rad-pack/runway/pull/806) by @Jade-GG



## v9.4.2 (2026-04-13)

### What's fixed
- Upgrade to Vite 8 [#804](https://github.com/statamic-rad-pack/runway/pull/804) by @duncanmcclean



## v9.4.1 (2026-04-13)

### What's fixed
- Replace spatie/ignition with spatie/error-solutions [#803](https://github.com/statamic-rad-pack/runway/pull/803) by @duncanmcclean



## v9.4.0 (2026-04-09)

### What's new
- Add disable duplicate action option [#801](https://github.com/statamic-rad-pack/runway/pull/801) by @AlexanderFalkenberg



## v9.3.3 (2026-04-03)

### What's fixed
- Hardened OrderBys [#798](https://github.com/statamic-rad-pack/runway/pull/798) by @duncanmcclean



## v9.3.2 (2026-03-27)

### What's fixed
- Fix Runway searchables in CP command palette search [#797](https://github.com/statamic-rad-pack/runway/issues/797) by @duncanmcclean



## v9.3.1 (2026-03-26)

### What's fixed
- Fix publish toggle for custom published columns [#794](https://github.com/statamic-rad-pack/runway/issues/794) by @sebabal
- Fix error on listing page when using model attribute as first column in blueprint [#793](https://github.com/statamic-rad-pack/runway/issues/793) by @andjsch



## v9.3.0 (2026-03-25)

### What's new
- Add live preview token support to GraphQL and REST API [#791](https://github.com/doublethreedigital/runway/issues/791) by @duncanmcclean

### What's fixed
- Fix validation on `published` field [#792](https://github.com/doublethreedigital/runway/issues/792) by @duncanmcclean



## v9.2.0 (2026-03-17)

### What's new
- Supports Laravel 13 [#781](https://github.com/doublethreedigital/runway/issues/781) by @duncanmcclean



## v9.1.1 (2026-03-13)

### What's fixed
- Fix error when duplicating model with "computed" title [#788](https://github.com/doublethreedigital/runway/issues/788) by @duncanmcclean



## v9.1.0 (2026-03-10)

### What's new
- Provide `isWorkingCopy` and `revisionsEnabled` to publish container context [#782](https://github.com/doublethreedigital/runway/issues/782) by @duncanmcclean
- Performance: prevent routingModel reinit [#785](https://github.com/doublethreedigital/runway/issues/785) by @macaws

### What's fixed
- Adopt `RunsUpdateScripts` trait [#778](https://github.com/doublethreedigital/runway/issues/778) by @duncanmcclean



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
