<?php

namespace StatamicRadPack\Runway\Widgets;

use Statamic\CP\Column;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;
use StatamicRadPack\Runway\Http\Controllers\CP\Traits\HasListingColumns;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class ResourceWidget extends Widget
{
    use HasListingColumns;

    protected static $handle = 'runway_resource';

    public function component()
    {
        $resource = $this->config('resource');

        if (! Runway::hasResource($resource)) {
            return VueComponent::render('dynamic-html-renderer', [
                'html' => "Error: Resource [$resource] doesn't exist.",
            ]);
        }

        $resource = Runway::findResource($resource);

        if (! User::current()->can('view', $resource)) {
            return;
        }

        [$sortColumn, $sortDirection] = $this->parseSort($resource);

        $columns = $resource->blueprint()
            ->columns()
            ->when($resource->hasPublishStates(), function ($collection) {
                $collection->put('status', Column::make('status')
                    ->listable(true)
                    ->visible(true)
                    ->defaultVisibility(true)
                    ->sortable(false));
            })
            ->only($this->config('fields', []))
            ->map(fn ($column) => $column->sortable(false)->visible(true))
            ->values();

        return VueComponent::render('runway-widget', [
            'resource' => $resource->handle(),
            'icon' => $resource->icon(),
            'title' => $this->config('title', $resource->name()),
            'additionalColumns' => $columns,
            'filters' => Scope::filters('runway', ['resource' => $resource->handle()]),
            'initialSortColumn' => $sortColumn,
            'initialSortDirection' => $sortDirection,
            'initialPerPage' => $this->config('limit', 5),
            'hasPublishStates' => $resource->hasPublishStates(),
            'titleColumn' => $this->getTitleColumn($resource),
            'canCreate' => User::current()->can('create', $resource)
                && $resource->hasVisibleBlueprint()
                && ! $resource->readOnly(),
            'listingUrl' => cp_route('runway.index', ['resource' => $resource->handle()]),
        ]);
    }

    /**
     * Parse sort column and direction, similar to how sorting works on collection tag.
     */
    protected function parseSort(Resource $resource): array
    {
        $default = "{$resource->orderBy()}:{$resource->orderByDirection()}";
        $sort = $this->config('order_by') ?? $this->config('sort') ?? $default;
        $exploded = explode(':', $sort);
        $column = $exploded[0];
        $direction = $exploded[1] ?? 'asc';

        return [$column, $direction];
    }
}
