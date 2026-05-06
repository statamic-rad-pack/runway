<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Runway\Http\Resources\CP\Model as ModelResource;
use StatamicRadPack\Runway\Resource;

class ModelRevisionsController extends CpController
{
    use Traits\ExtractsFromModelFields;

    public function index(Request $request, Resource $resource, Model $model)
    {
        $revisions = $model
            ->revisions()
            ->reverse()
            ->prepend($this->workingCopy($model))
            ->filter()
            ->each(function ($revision) use ($model) {
                $revision->attribute('item_url', $model->runwayRevisionUrl($revision));
            });

        // The first non manually created revision would be considered the "current"
        // version. It's what corresponds to what's in the content directory.
        optional($revisions->first(function ($revision) {
            return $revision->action() != 'revision';
        }))->attribute('current', true);

        return $revisions
            ->groupBy(function ($revision) {
                return $revision->date()->clone()->startOfDay()->format('U');
            })->map(function ($revisions, $day) {
                return compact('day', 'revisions');
            })->reverse()->values();
    }

    public function store(Request $request, Resource $resource, Model $model)
    {
        $model->createRevision([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new ModelResource($model);
    }

    public function show(Request $request, Resource $resource, Model $model, $revisionId)
    {
        $revision = $model->revision($revisionId);
        $model = $model->makeFromRevision($revision);

        // TODO: Most of this is duplicated with EntriesController@edit. DRY it off.

        $blueprint = $model->runwayResource()->blueprint();

        [$values, $meta] = $this->extractFromFields($model, $resource, $blueprint);

        return [
            'title' => $model->getAttribute($resource->titleField()),
            'editing' => true,
            'actions' => [
                'save' => $model->runwayUpdateUrl(),
                'publish' => $model->runwayPublishUrl(),
                'unpublish' => $model->runwayUnpublishUrl(),
                'revisions' => $model->runwayRevisionsUrl(),
                'restore' => $model->runwayRestoreRevisionUrl(),
                'createRevision' => $model->runwayCreateRevisionUrl(),
            ],
            'values' => $values,
            'meta' => $meta,
            'permalink' => $resource->hasRouting() ? $model->uri() : null,
            'resourceHasRoutes' => $resource->hasRouting(),
            'blueprint' => $blueprint->toPublishArray(),
            'resource' => $resource,
            'readOnly' => true,
        ];
    }

    protected function workingCopy(Model $model)
    {
        if ($model->published()) {
            return $model->workingCopy();
        }

        return $model
            ->makeWorkingCopy()
            ->date($model->updated_at)
            ->user(null);
    }
}
