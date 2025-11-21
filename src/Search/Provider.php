<?php

namespace StatamicRadPack\Runway\Search;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Statamic\Search\Searchables\Provider as BaseProvider;
use Statamic\Support\Str;
use StatamicRadPack\Runway\Runway;

class Provider extends BaseProvider
{
    protected static $handle = 'runway';

    protected static $referencePrefix = 'runway';

    public function provide(): LazyCollection
    {
        $resources = $this->usesWildcard()
            ? Runway::allResources()->keys()
            : $this->keys;

        $models = (new LazyCollection($resources))
            ->flatMap(function ($handle) {
                return Runway::findResource($handle)
                    ->newEloquentQuery()
                    ->whereStatus('published')
                    // todo: query scope
                    ->lazy(config('statamic.search.chunk_size'));
            });

        if ($this->hasFilter()) {
            return $models->filter($this->filter())->values()->map->reference();
        }

        return $models->map->reference();
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof Searchable) {
            return false;
        }

        $resource = $searchable->resource();

        if (! $this->usesWildcard() && ! in_array($resource->handle(), $this->keys)) {
            return false;
        }

        return $this->filter()($searchable);
    }

    public function find(array $keys): Collection
    {
        return collect($keys)
            ->groupBy(fn ($key) => Str::before($key, '::'))
            ->flatMap(function ($items, $handle) {
                $ids = $items->map(fn ($item) => Str::after($item, '::'));

                return Runway::findResource($handle)->model()->whereStatus('published')->find($ids);
            })
            ->mapInto(Searchable::class);
    }
}
