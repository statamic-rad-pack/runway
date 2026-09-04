<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Structures\RunwayStructure;

class ReorderModelsController extends CpController
{
    public function __invoke(Request $request, Resource $resource)
    {
        abort_unless($resource->orderable(), 404);

        $this->authorize('reorder', $resource);

        $request->validate([
            'ids' => 'required|array',
            'page' => 'required|integer',
            'perPage' => 'required|integer',
        ]);

        $ids = $resource->newEloquentQuery()
            ->when($resource->model()->hasNamedScope('runwayListing'), fn ($query) => $query->runwayListing())
            ->runwayOrderBy('order')
            ->toBase()
            ->pluck($resource->qualifiedPrimaryKey())
            ->map(fn ($id) => (string) $id);

        $offset = ($request->page - 1) * $request->perPage;

        $submitted = collect($request->ids)->map(fn ($id) => (string) $id);
        $current = $ids->slice($offset, $request->perPage);

        // If the submitted ids aren't a rearrangement of the page being reordered, the
        // listing was out of date. Continuing would duplicate and drop models.
        if ($submitted->sort()->values()->all() !== $current->sort()->values()->all()) {
            abort(409, __('The listing is out of date. Refresh the page and try reordering again.'));
        }

        $reordered = $ids->values()->all();

        foreach ($submitted as $index => $id) {
            $reordered[$offset + $index] = $id;
        }

        $modelType = $resource->model()->getMorphClass();

        $existing = RunwayStructure::query()
            ->where('model_type', $modelType)
            ->pluck('order', 'model_id');

        $rows = collect($reordered)
            ->map(fn ($id, $index) => ['model_type' => $modelType, 'model_id' => $id, 'order' => $index + 1])
            ->reject(fn ($row) => (int) $existing->get($row['model_id']) === $row['order'])
            ->values()
            ->all();

        RunwayStructure::upsert($rows, ['model_type', 'model_id'], ['order']);
    }
}
