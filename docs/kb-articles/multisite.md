---
title: Multisite
---

Out of the box, Runway doesn't come with multisite support as Runway doesn't assume anything about how your models are created.

However, there is an easy way to scope the model results you get to only those related to the currently selected site.

1. Add a column to your model that'll contain a site handle.

2. Once created, add the following scope to your model. Be sure to change `site` to the handle of the column you just created.

```php
// app/Models/Post.php

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

#[Scope] // [tl! focus:start]
protected function runwayListing(Builder $query): void
{
    $query->where('site', Site::selected()->handle());
} // [tl! focus:end]
```

3. Now, when you go to your model's listing page, the returned results should relate to the currently selected site.

## Localisations

Sorry, Runway doesn't support creating localisations of models as we've built Runway to be as unopinionated as possible, which introducing this feature would go against.
