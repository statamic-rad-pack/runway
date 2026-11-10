# Relationships acceptance site

An isolated, disposable Statamic app linked to this Runway checkout. It does not use an existing developer's database or sandbox. Generated app files and dependencies live in ignored `site/`.

```sh
bash dev/relationships/setup.sh --reset
```

The reset option rebuilds only this site's fixture tables and content. Run without `--reset` to retain edits. To reset fixtures without reinstalling dependencies, run `php dev/relationships/seed.php`.

Serve `dev/relationships/site/public` with PHP and use the normal Runway asset workflow. If a Runway Vite server is already running, setup copies `dist/hot` into the site's `public/vendor/runway/hot`; ensure that server allows this site's origin. Otherwise publish the built Runway assets. No assets are built by the setup script.

In an Amp orb, start the PHP service from the repository:

```sh
amp orb service start relationships --command 'APP_URL="$PUBLIC_URL" php dev/relationships/site/artisan serve --host=0.0.0.0 --port="$PORT"' --port 8001 --portal
```

Use the returned portal, not a localhost URL. Open `/__dev/login/admin@example.test` to sign in, or `/__dev/login/viewer@example.test` for a view-only resource user. Both fixture accounts have the password `password`. The development login route exists only in the local environment. Never deploy this fixture app.

## Fixtures

- Entry: `/cp/collections/pages/entries/models-acceptance`.
- Authors: Ada Lovelace (1), Grace Hopper (2), Katherine Johnson (3).
- Notebooks: JSON-backed selections, deliberately no Eloquent relation methods.
- Term: Topics → Models. Global: Models. User: admin. Asset: `fixture.svg`.
- Entry includes singular, ordered and unordered plural, dropdown, typeahead, unavailable ID `9999`, imported fieldset, Grid, Replicator and Bard.
- `/acceptance` renders Antlers; `/models` renders Blade; `/acceptance-data` returns augmented singular/plural values. Normal REST and GraphQL endpoints are enabled for these local fixtures.

## Manual acceptance

1. Open the entry. Verify the singular author, stored plural order, dropdown/typeahead, nested values and invalid item. Only reorderable selectors have drag handles.
2. Select another author, save, reopen, reorder the ordered list, save again, then clear singular/plural selections. Check processed values are a scalar/`null` and array/`[]`; file repositories may omit cleared values. Other authors must not change.
3. Create and edit an author inline. Those explicit actions may change authors; selecting and unlinking must not. There must be no delete-on-unlink prompt.
4. Repeat select/save/reopen on the term, global, user, asset and JSON notebook. Inspect the notebook's `selections` column; no relationship method is needed.
5. Reorder nested rows and selections, including an existing unavailable reference. Saving unrelated changes must retain it. An arbitrary new missing ID must fail validation.
6. Sign in as the viewer. Viewable author labels remain available, but resource creation/editing is forbidden. PHPUnit additionally checks denied selection, scope restrictions and protected placeholders.
7. Compare `/acceptance`, `/models`, REST `/api/collections/pages/entries/models-acceptance` and GraphQL singular/list results with stored IDs. Enable only the API endpoints you intend to expose in a real app.

REST also exposes these selections on the term, global, asset and notebook fixtures. Statamic's default REST user resource excludes custom fields; user selections are available through GraphQL and augmentation instead.

Useful GraphQL query:

```graphql
{
  entry(id: "models-acceptance") {
    ... on Entry_Pages_Models {
      single { name }
      ordered { name }
    }
  }
  notebooks {
    data { selections { single { name } ordered { name } } }
  }
}
```

SQL-backed Statamic repositories require their own Eloquent driver and tables; this fixture uses file-backed Statamic content and an Eloquent JSON host. Targeted tests cover the fieldtype's model-host path without assuming Runway's trait or relation methods.

The SQL entry path was also checked with Eloquent Driver 5.12.5 and a disposable SQLite entries table: select/save/reopen/reorder/clear/augment, stale references, and scalar/plural filters all passed without changing target rows. That check switched the repository only within its PHP process; the review site remains file-backed.
