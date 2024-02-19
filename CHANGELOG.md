# Changelog

## v6.2.1 (2024-02-19)

### What's fixed
* Fixed sort order handling when user has defined sort and no `orderBy` has been set  #438 by @ryanmitchell



## v6.2.0 (2024-02-16)

### What's improved
* The `runwayListing` scope can now specify the default `orderBy` of queries #434 by @ryanmitchell
* Improved augmentation of relationships #430 #435 by @duncanmcclean



## v6.1.0 (2024-02-11)

### What's new
* Appended attributes are now included in augmentation #423 by @ryanmitchell
* Added `.editorconfig` and installed Laravel Pint #424 by @ryanmitchell

### What's fixed
* Fixed infinite loop getting/setting `slug` on Eloquent models with `RunwayRoutes` trait #429 by @duncanmcclean
* Fixed relationship fields being pre-filled when they shouldn't be #428 by @duncanmcclean
* Fixed augmentation for `BelongsToMany` relationships #425 by @mefenlon



## v6.0.6 (2024-02-02)

### What's fixed
* Fixed `MorphMany` relationships not being augmented #419 by @mefenlon



## v6.0.5 (2024-02-01)

### What's improved
* Added a progress bar to the `runway:rebuild-uris` command #414 by @duncanmcclean
* Improved the way relationships are eager loaded #418 by @duncanmcclean

### What's fixed
* Fixed `MorphToMany` relationships not being augmented #416 by @mefenlon
* Fixed an issue saving the Has Many fieldtype #413 by @duncanmcclean



## v6.0.4 (2024-01-22)

### What's fixed
* Fixed saving models via relationship fieldtypes #407 by @duncanmcclean



## v6.0.3 (2024-01-16)

### What's fixed
* Fixed model accessors not being augmented #405 by @duncanmcclean



## v6.0.2 (2024-01-15)

### What's new
* Added "Edit Blueprint" link to index & edit pages #400 by @duncanmcclean

### What's improved
* The model ID is now passed to the publish form so it exists in Vuex #401 by @duncanmcclean

### What's fixed
* Fixed an issue with CP nav icons not displaying #399 by @caseydwyer



## v6.0.1 (2024-01-12)

### What's improved
* The default `runway.php` config file no longer includes a blueprint by @duncanmcclean

### What's fixed
* Fixed styling of "Visit URL" button by @duncanmcclean
* Fixed some issues with listing columns & preferences by @duncanmcclean
* Fixed `doctrine/dbal` dependency by @duncanmcclean



## v6.0.0 (2024-01-12)

### Read First 👀
Be sure to read the [Upgrade Guide](https://runway.duncanmcclean.com/upgrade-guides/v5-x-to-v6-0) first as manual changes may be necessary. You can also read the [Launch Discussion](https://github.com/statamic-rad-pack/runway/discussions/396) to learn about many of the changes in depth.

### What's new
* Runway is now part of the [Rad Pack](https://github.com/statamic-rad-pack) 🚀 #394 by @duncanmcclean
* You can now edit blueprints in the Control Panel #373 by @duncanmcclean
* Added "Duplicate" action to listing tables #374 by @duncanmcclean
* Runway now includes support for Statamic's REST API feature #356 by @ryanmitchell

### What's improved
* Runway will now refer to "models" instead of "records" #375 by @duncanmcclean
* Runway should now have better support for fieldsets #362
* Augmentation is now done *properly* #354 by @duncanmcclean
* Lots of refactoring & added tests to make things nicer going forward!

### What's changed
* The "Table" mode in the Has Many fieldtype has been removed. #360 by @duncanmcclean



## v5.6.1 (2024-01-09)

### What's fixed
* Fix "Listable: Hidden" fields not showing in column picker #392 by @caseydwyer



## v5.6.0 (2023-12-23)

### What's improved
* Eloquent model is now passed to the `ResourcePolicy` #386 by @edalzell



## v5.5.3 (2023-12-16)

### What's fixed
- Fixed `unique` validation rule #388 by @duncanmcclean

## v5.5.2 (2023-12-01)

### What's fixed

* Fixed the "parent" not being passed through to fields on publish form pages #379 #381 by @duncanmcclean

## v5.5.1 (2023-11-28)

### What's fixed

* Fixed error when search index returned Runway results #376 #377 by @duncanmcclean

## v5.5.0 (2023-11-16)

### What's new

* Columns cast as `object` in the Eloquent model will be treated by Runway as an `array` #370 by @caseydwyer

## v5.4.1 (2023-11-03)

### What's fixed

* Search on the Listing Table will no longer error when you have imported fields #355 by @edalzell

## v5.4.0 (2023-10-20)

### What's new

* Added support for a new `save: false` config option on fields to disable field values being saved #353 by @edalzell

### What's improved

* When a resource has no records, you'll now see the listing table rather than an empty state #352 by @duncanmcclean

## v5.3.3 (2023-10-07)

### What's fixed

* 2nd attempt at fixing an issue where query parameters weren't being preserved in pagination links when using the Runway tag #349 #351

## v5.3.2 (2023-10-03)

### What's fixed

* Fixed an issue where *sometimes*, under certain circumstances, you'd get an error with the HasMany fieldtype #342

## v5.3.1 (2023-10-02)

### What's fixed

* Query parameters are now preserved when using pagination with the Runway tag #349 #350 by @duncanmcclean
* A 404 is now thrown when a record can't be found in the Control Panel, rather than an error #347 by @duncanmcclean

## v5.3.0 (2023-09-20)

### What's new

* Added `singular`/`plural` configuration options to resources #339 by @duncanmcclean
* Migrations are now generated using the anonymous class syntax #332 by @duncanmcclean

### What's improved

* When generating migrations from a blueprint & a fieldtype can't be mapped to a column type, Runway will now add a `TODO` for you to complete the migration #334 by @duncanmcclean

### What's fixed

* Ensure migrations aren't run if the user didn't ask for them to be run #333 by @duncanmcclean
* Fixed UI issues with the sidebar on the publish from #338 by @duncanmcclean

## v5.2.2 (2023-09-10)

### What's fixed

* Fixed validation issues #321 #324 by @duncanmcclean
* Fixed early return when discovering blueprints that was causing an error #322 #323 by @duncanmcclean

## v5.2.1 (2023-09-06)

### What's fixed

* Fixed blueprint-related error when publishing Eloquent Driver migrations #319 #320 by @duncanmcclean

## v5.2.0 (2023-08-07)

### What's new

* Improved filtering Has Many / Belong To fields #313 by @duncanmcclean

### What's fixed

* Fixed an issue where appended attributes would appear as filterable in "Fields" filter #316 #317 by @duncanmcclean

## v5.1.2 (2023-08-07)

### What's improved

* Removed old dependency

## v5.1.1 (2023-08-04)

### What's fixed

* Fixed `{{ url }}` not being available in search results #314 #315 by @duncanmcclean
* Fixes error when trying to paginate search results #311 #312 by @ryanmitchell

## v5.1.0 (2023-07-26)

### What's new

* You can now add nested fields from inside JSON columns to your blueprint #302 #308 by @morphsteve

## v5.0.10 (2023-06-19)

### What's fixed

* Fieldtype: Dropdown / Stack options will now respect the configured ordering for the resource #298

## v5.0.9 (2023-06-16)

### What's fixed

* Fixed issue with "Fields" filter on non-Runway listing tables #292
* Fieldtypes: Ensure pagination only takes place when in Stack mode #289 #291 by @edalzell
* Removes extra border on Publish Form #296

### What's improved

* Has Many Fieldtype: "Reorderable" setting now works with pivot tables #287 #297

## v5.0.8 (2023-06-14)

### What's new

* PHP 8.1 is now supported again! #293

### What's improved

* Documentation: Updated pagination templating example #284 by @conradfuhrman

## v5.0.7 (2023-06-01)

### What's fixed

* Fixes GraphQL naming issues #279 #283 by @Buffalom
* Fixed issue where relationship field was being picked up as the first column #277 #280

## v5.0.6 (2023-05-24)

### What's new

* Enabled filters on the Listing Table #276 #278

## v5.0.5 (2023-05-24)

### What's improved

* Added pagination to fieldtype selection listings #275

## v5.0.4 (2023-05-09)

### What's fixed

-   Fixed slightly broken listing table #267
-   Fixed issue with save button icon #269
-   Allow overriding Eloquent relationship names in Belongs To fieldtype #268 #270

## v5.0.3 (2023-05-08)

### What's fixed

-   Fix hard-coded primary key column in HasMany fieldtype #264 #265

## v5.0.2 (2023-05-05)

### What's new

-   Added a new `select` parameter to the Runway tag #263

## v5.0.1 (2023-05-04)

### What's new

-   You can now re-order items using the HasMany fieldtype #228 #259

### What's fixed

-   Fixed GraphQL not resolving the Entries fieldtype (and probably others too!) #258 #260

## v5.0.0 (2023-04-28)

### What's new

-   Runway v5 now supports Statamic 4 #235

### What's fixed

-   Fixed GraphQL error on some fieldtypes #252 #253

### Breaking changes

Please add the new `HasRunwayResource` trait to the Eloquent models you have configured in the Runway config.

```php
// app/Models/Order.php

class Order extends Model
{
    use HasRunwayResource;
}
```

## v4.2.5 (2023-04-21)

### What's fixed

-   Table name is now prefixed on two queries in the `ResourceController` #254

## v4.2.4 (2023-04-15)

### What's fixed

-   Slug field will be used for `slug` column when generating blueprint from Eloquent model #251

## v4.2.3 (2023-04-08)

### What's fixed

-   Fixed "Column index out of range" error #242
-   Various fixes to Table mode on the Has Many fieldtype #246
-   Fixed an issue where Runway would try to save attributes from the `$appends` array #247
-   Fixed an 'ambiguous table' error with one of Runway's queries #248
-   Fix double requests, caused by column data changing behind the scenes #250

### What's improved

-   Breadcrumbs are passed into the inline publish form (the one that shows when you edit something in a stack) #249

## v4.2.2 (2023-02-28)

### What's fixed

-   Fixed another issue with auto-publishing of Runway's assets

## v4.2.1 (2023-02-28)

### What's fixed

-   Removed `console.log` I left in the last release 🤦‍♂️
-   Fixed an issue with auto-publishing assets while waiting on statamic/cms#7627 to be merged

## v4.2.0 (2023-02-28)

Nothing new since v4.1, just a couple of tweaks to Runway behind the scenes.

### What's new

-   Switched from Laravel Mix to Vite #229 by @duncanmcclean
-   Replaced StyleCI with Pint via GitHub Actions

## v4.1.2 (2023-02-20)

### What's new

-   You can now relationship fields when using the `where` parameter on the Runway tag #225 by @duncanmcclean

### What's fixed

-   Runway no longer attempts to search section & computed fields when searching in the Control Panel #224 by @stoffelio

## v4.1.1 (2023-02-18)

### What's fixed

-   Ignore computed fields when saving #222 #223 by @ryanmitchell

## v4.1.0 (2023-02-11)

### What's improved

-   Added a `ResourcePolicy` to handle authorization in Runway #221 by @MtDalPizzol

### What's fixed

-   Prevent resource row selection #220 by @MtDalPizzol

## v4.0.0 (2023-02-05)

### What's new

-   You may now translate permission names #214 by @MtDalPizzol

### Breaking changes

-   Permission keys have changed. If you have a user role with custom permissions set, you may need to make some changes:
    -   **File driver:** If you're using the file users repository, your `roles.yaml` file will have been updated automatically with the new permission names.
    -   **Otherwise:** You'll need to change permission names yourself - examples of the changes are shown below.
        -   `View Products` -> `view product`
        -   `Edit Products` -> `edit product`
        -   `Create new Product` -> `create product`
        -   `Delete Product` -> `delete product`

## v3.0.3 (2022-02-05)

### What's improved

-   Improved handling of pagination in Runway tag to make it more Statamic-like #212 #217 by @duncanmcclean

### What's fixed

-   Fixed `sort` parameter in Runway tag #213 #215 by @duncanmcclean
-   Fixed action controller returning incorrect items to actions #216 by @Alt-Ben

## v3.0.2 (2023-01-28)

### What's fixed

-   Fixed an issue where the stacks on relationship fieldtypes would open up empty #207 #208 by @duncanmcclean

## v3.0.1 (2022-01-27)

### What's fixed

-   Fixed an issue where 'Edit' button wouldn't show in listing table action list if front-end routing was disabled #205 #206 by @duncanmcclean

## v3.0.0 (2023-01-27)

### What's new

-   Support for Statamic 3.4! 🚀 #201 by @duncanmcclean
-   You may now use Statamic's Search functionality with Runway #169 #200 by @jasonvarga
-   You may now configure the "title" field used for models in fieldtypes & in search #202 by @duncanmcclean

### Breaking changes

-   Statamic 3.3 is no longer supported. You should upgrade to receive any new features & bug fixes.
-   Runway has dropped support for setting title & section of Control Panel nav items, in favour of Statamic's new CP Nav Preferences feature #204
-   If you were setting a custom icon for Control Panel nav items, you will need to adjust your config. Please [review this PR](https://github.com/duncanmcclean/runway/pull/204) for more details.

## v2.6.7 (2023-01-28)

### What's fixed

-   Fixed an issue where the stacks on relationship fieldtypes would open up empty #207 #208 by @duncanmcclean

## v2.6.6 (2023-01-27)

### What's fixed

-   Fixed an issue where 'Edit' button wouldn't show in listing table action list if front-end routing was disabled #205 #206 by @duncanmcclean

## v2.6.5 (2022-01-24)

### What's new

-   Added Save Button Options #198 by @duncanmcclean

### What's fixed

-   Fixed issue where fallback method calls to `Resource` weren't being passed through correctly

## v2.6.4 (2023-01-20)

### What's new

-   PHP 8.2 is now officially supported! #191

### What's fixed

-   Fix data being encoded by Runway even when a cast is setup #197
-   Ensure BelongsTo values wrapped in arrays are always converted to a single item

## v2.6.3 (2022-12-21)

### What's improved

-   Made some minor tweaks - nothing noticeable #189

## v2.6.2 (2022-12-07)

### What's fixed

-   Fix error with BelongsTo fieldtype when saving via a publish form inside a stack #187 #188

## v2.6.1 (2022-11-19)

### What's fixed

-   The "Display" text for a field is now shown as the column name when using the 'Table' mode on the fieldtype (instead of the handle!) #185 #186

## v2.6.0 (2022-11-05)

**⚠️ This release contains breaking changes.**

### What's fixed

-   Bulk actions are no longer visible for read-only resources #181

### Breaking changes

-   Dropped support for anything below PHP 8.1, Laravel 8 & Statamic 3.3 #182

## v2.5.6 (2022-11-03)

### What's fixed

-   Fixed an issue where the translations weren't working properly on Save buttons #179 by @jymden

## v2.5.5 (2022-11-03)

### What's new

-   All of Runway's strings are now translatable! #178

## v2.5.4 (2022-10-29)

### What's fixed

-   The 'Save' button will now show when viewing the publish form on a small-ish screen #172 #174

## v2.5.3 (2022-09-27)

### What's improved

-   When using static caching, model URLs will automatically be invalidated on save - no need to configure a rule for it! #166

## v2.5.2 (2022-09-26)

### What's improved

-   It's now possible to invalidate URLs in your static cache for front-end routes #166 #167

## v2.5.1 (2022-08-24)

### What's fixed

-   Fixed issue when generating listing columns, if you're using a fieldset in your blueprint #164 #165

## v2.5.0 (2022-08-06)

### What's new

-   Brand new 'Table mode' in Has Many fieldtype #163

### What's fixed

-   Stacks opened by Runway's fieldtypes will now show fieldtype's index components, if they have one #162

## v2.4.3 (2022-07-23)

### What's new

-   You may now specify the order & direction of models via the Runway config #160 #161

## v2.4.2 (2022-07-15)

### What's fixed

-   Fixed an error when attempting to augment model that doesn't exist #156 #158
-   Fixed an error when using the `{{ nav:breadcrumbs }}` tag on front-end routes #157 #159

## v2.4.1 (2022-07-06)

### What's fixed

-   Fieldtypes: Formatted title will now also be returned from `toItemArray` method #155 by @edalzell

## v2.4.0 (2022-06-30)

### What's new

-   You can now specify a `title_format` on Runway's fieldtypes #153 #154 by @edalzell

## v2.3.9 (2022-06-13)

### What's fixed

-   If you had a `runwaySearch` scope on your model & tried to search using one of Runway's fieldtypes, Runway's default logic would still be used #149 #150 by @duncanmcclean

## v2.3.8 (2022-06-07)

### What's fixed

-   Fixed an issue where columns with `json` cast would be 'double cast' when saved to the database #147 by @duncanmcclean

## v2.3.7 (2022-06-07)

### What's fixed

-   Fixed a caching issue with fieldtype eager loading if the same model is augmented multiple times during a request #146 by @duncanmcclean

## v2.3.6 (2022-06-01)

### What's new

-   You may now specify relationships to be eager loaded when a Runway field is augmented #145 by @duncanmcclean

## v2.3.5 (2022-04-28)

### What's fixed

-   Fixed an issue with search on Runway fieldtypes - they'll no longer attempt to search hidden fields.

## v2.3.4 (2022-04-26)

### What's new

-   You may now specify a `runwayListing` scope on your model to filter the results returned in the CP Listing Table #142 by @ryanmitchell

### What's improved

-   There's now an option for toggling 'Create' button on BelongsTo fieldtype

### What's fixed

-   Fixed an issue where search didn't work on fieldtypes

## v2.3.3 (2022-04-14)

### What's new

-   Better customisation around CP Nav Items #141 by @duncanmcclean
-   Resources can now be set to 'read-only' #139 by @duncanmcclean
-   Added a new `runway:resources` command to show all registered resources #137 by @duncanmcclean

### What's improved

-   Removed some old code (from before the fancy listing table) #140 by @duncanmcclean

## v2.3.2 (2022-04-13)

### What's fixed

-   Fixed an issue loading resource results when you're using a fieldset in the related blueprint #136 by @Skullbock

## v2.3.1 (2022-04-08)

### What's improved

-   Improved the performance of augmentation for Runway Fieldtypes (if you load the same record in multiple times) #135 by @ryanmitchell

## v2.3.0 (2022-02-26)

### What's new

-   Statamic 3.3 is now supported! #120 by @duncanmcclean 🚀
-   Related fields will now be automatically set when creating with the BelongsTo fieldtype #112 #124 by @duncanmcclean

### What's fixed

-   Fixed permissions issues on the Listing Table actions & in the Control Panel Nav #119 by @jbfournot

### What's improved

-   Eager loading magic has been refactored & is now backed up with some tests! #123 by @duncanmcclean

### Breaking changes

-   Statamic 3.1 is no longer supported

Also, thanks to @SimonJnsson for a small documentation update!

## v2.2.5 (2022-02-15)

### What's fixed

-   Another fix for the magic behind Runway's "relation guessing" code for eager loading #118 by @duncanmcclean

## v2.2.4 (2022-02-09)

### What's new

-   You may now manually specify relations to be eager loaded, if you'd prefer to have complete control by @duncanmcclean

## v2.2.3 (2022-02-09)

### What's fixed

-   The 'magic' behind the eager loading wasn't always resolving relation name's correctly by @duncanmcclean

## v2.2.2 (2022-02-08)

### What's fixed

-   Fixed a couple of eager loading issues #116 #117 by @duncanmcclean

## v2.2.1 (2022-02-05)

### What's improved

-   Added some eager loading to 'index queries' #113 #114 by @duncanmcclean and @DanielDarrenJones

## v2.2.0 (2022-01-29)

### What's new

-   PHP 8.1 Support
-   You may now add the 'Has Many' fieldtype to entries/taxonomies/globals #109 by @ryanmitchell
-   The HasMany fieldtype now has an option in the Blueprints UI to toggle on/off resource creation #108 by @ryanmitchell

### Breaking changes

-   Dropped support for Laravel 6 & Laravel 7. [You should upgrade to Laravel 8](https://laravel.com/docs/8.x/upgrade).

## v2.1.37 (2021-12-20)

### What's new

-   Fixed issue viewing listing tables when using Eloquent users

## v2.1.36 (2021-12-13)

### What's new

-   You can now use the Has Many fieldtype for 'Many to Many' relationships #102 by @psyao

## v2.1.35 (2021-11-24)

### What's fixed

-   Fixed CP permission issues #101 by @edalzell

## v2.1.34 (2021-11-16)

### What's fixed

-   Fixed issue when searching a resource with a 'Has Many' fieldtype #99 #100 by @edalzell

## v2.1.33 (2021-11-15)

### What's fixed

-   Fixed 'Too few arguments' error with `ResponseCreated` event
-   Fixed URIs being saved in a bad format if you don't start your route with `/` #98

## v2.1.32 (2021-10-30)

### What's fixed

-   Fixed filenames of migrations generated by the `blueprint -> migration` tool #96

## v2.1.31 (2021-10-29)

### What's improved

-   Enabled Bulk Actions on the Runway Listing Table #87

## v2.1.30 (2021-10-23)

### What's fixed

-   Fixed a few round edges with permissions #94

## v2.1.29 (2021-10-22)

### What's improved

-   Made some improvements to the Belongs To fieldtype, fixing an issue in the process #93 #91

## v2.1.28 (2021-10-20)

### What's fixed

-   Fixed an issue viewing resources in the CP, where casting dates to `date_immutable` would cause issues. #89
-   GraphQL queries now use the built-in `QueriesConditions` trait for filtering, not custom code

## v2.1.27 (2021-09-25)

### What's new

-   🎉 GraphQL API ([read the docs](https://runway.duncanmcclean.com/graphql)) #86 #54

## v2.1.26 (2021-09-24)

### What's new

-   You can now use Eloquent Query Scopes with the Runway tag #82

## v2.1.25 (2021-09-24)

### What's new

-   You can now eager load relationships using the `with` parameter on the Runway tag #84

## v2.1.24 (2021-09-20)

### What's new

-   You can now generate blueprints from Eloquent models #58

## v2.1.23 (2021-09-18)

### What's new

-   You can now create/edit models from Runway's relationship fieldtypes #76

## v2.1.22 (2021-09-14)

### What's fixed

-   Fieldtype values are now flagged as invalid if model can't be found #78
-   Fixed issue where 'Delete' action would not be available anywhere, apart from in Runway #79

## v2.1.21 (2021-09-09)

### What's new

-   Added HasMany fieldtype #74

## v2.1.20 (2021-08-27)

### What's fixed

-   Fixed issue with the post-select state of dropdown fieldtypess #67

## v2.1.19 (2021-08-20)

### What's fixed

-   Fixed issues with 'primary columns' in the listing table

## v2.1.18 (2021-08-19)

**Why have you missed a couple of versions?** I tagged v2.1.7 as v2.1.17 by mistake and so to fix that, I'm tagging this as v2.1.18 which should make Packagist (and everywhere else) happy.

### What's new

-   Support for [Statamic 3.2](https://statamic.com/blog/statamic-3.2-beta)

### What's fixed

-   Fixed issue where the first field in a blueprint would be used as a primary column, where it should actually be a Relationship fieldtype.

## v2.1.10 (2021-08-12)

### What's fixed

-   Fixed another bug affecting third-party packages (Laravel Nova this time)

## v2.1.9 (2021-08-11)

### What's fixed

-   If you still have the `Responsable` interface on a model, you shouldn't get an error
-   Fixed issue with old usage of Runway tag

## v2.1.8 (2021-08-11)

Re-tag of previous release, v2.1.7.

## v2.1.7 (2021-08-11)

### What's fixed

-   Fixed an issue where the `Responsable` interface on models was causing issues (eg. with Inertia.js) #71

## v2.1.6 (2021-08-10)

### What's new

-   Launched a [new documentation site](https://runway.duncanmcclean.com) for Runway! 🚀
-   Added support for filters in the Listing Table #66
-   For resources with multiple words in their handle, like `FooBar`, you can now reference them in Antlers with `{{ runway:foo_bar }}` #69
-   You can now override the handle of a resource, just add `handle` to the resource's config
-   You can now use a custom scope to handle Runway searches (useful for querying related models) #65

### What's fixed

-   When a resource has no results, show a plural title

## v2.1.5 (2021-07-30)

### What's fixed

-   Runway will now no longer 'double encode' JSON if you've added a cast to your model #62
-   Fixed issue where updating models wouldn't work, if your model had a custom route key set

## v2.1.4 (2021-07-29)

### What's fixed

-   Updated the way we handle dates on the edit resource page #60
-   Runway will now throw the `ResourceNotFound` exception when a resource can not be found.
-   Fixed a console error that popped up when configuring listing columns #61
-   Little tiny other fix (probably didn't affect anyone - was related to something inside Runway's Vue components) #59

## v2.1.3 (2021-07-24)

### What's new

-   You can now generate migrations from an existing blueprint #56

## v2.1.2 (2021-07-07)

### What's fixed

-   You'll no longer get an error when editing a model if you have `getRouteKeyName` defined on your model. #53
-   Fixed an issue where a fieldtype's `Index` component would not be rendered #52

## v2.1.1 (2021-07-06)

### What's fixed

-   Listing rows will now be properly displayed with `preProcessIndex` (it'll fix the display of Date fields) #52

## v2.1.0 (2021-07-03)

**⚠️ This update contains breaking changes.**

### What's new

-   A brand new Listing table for your models, just like the one used for entries #15
-   You can now use real [Actions](https://statamic.dev/extending/actions#content), instead of 'Listing buttons'

### Breaking changes

**Listing Columns & Sorting**

The `listing.columns` and `listing.sort` configuration options have been removed. Columns and sorting are now configured from the listing table itself, in the same way it works for entries.

**Listing buttons**

This release removes the 'listing buttons' functionality, in place of Statamic's [Actions](https://statamic.dev/extending/actions#content) feature. Any listing buttons will no longer work. It's recommended you refactor into an Action during the upgrade process.

## v2.0.6 (2021-06-30)

### What's fixed

-   Fixes issue with dirty state when creating model #41
-   If it's a JSON field, make sure it's decoded before passing it to the publish form #40

## v2.0.5 (2021-06-16)

### What's fixed

-   Fixed issues around 'primary key' stuff #39

## v2.0.4 (2021-06-04)

### What's fixed

-   If there's no sidebar, we won't try and show one #38
-   Fix an issue where the slug fieldtype failed to load #31
-   Actually process fieldtypes in the resource listing #37

### What's improved

-   Runway now has some defaults for 'Listing Columns' and 'Listing Sort', in case its not present in the config
-   The Belongs To fieldtype will now give you a link to edit the related model

## v2.0.3 (2021-05-26)

### What's fixed

-   White screen on publish form pages #36

## v2.0.2 (2021-05-25)

### What's fixed

-   Another fix for the `dist/js/cp.js` error

## v2.0.1 (2021-05-25)

### What's fixed

-   Fixed undefined method `uri()` exception when editing record.
-   Now ignores the `.png` files when pulling down a release. (Probably not noticable)
-   Only boots into the `DataRepository` if routing is enabled on at least one resource.
-   _Hopefully_ fix the `Can't locate path <.../dist/js/cp.js>` error when installing.

## v2.0.0 (2021-05-24)

Runway 2 introduces some minor breaking changes. Including a minimum requirement for Statamic 3.1 and the fact `models` are now called `resources` in the config (which our upgrade script should automate for you).

### What's new?

-   [Front-end routing](https://github.com/duncanmcclean/runway/tree/2.0#routing)

### What's improved?

-   Models are now 'Resources' - this will be reflected in your config file, it's essentially to stop you getting mixed up between a Runway Model and an Eloquent model
-   Resources aren't just a big old arrays anymore 😅
-   The Publish Forms in the CP are now Runway's (to allow for extra functionality)

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

-   Upgrade script to change `models` to `resources` in config
-   Ability to disable Runway's migrations

## v2.0.0-beta.1 (2021-05-15)

Initial beta release for v2.0 - view [release notes for v2.0](https://github.com/duncanmcclean/runway/blob/2.0/CHANGELOG.md#v200-2021-xx-xx)
