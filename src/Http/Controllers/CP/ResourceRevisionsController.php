<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Runway\Http\Resources\CP\Model;
use StatamicRadPack\Runway\Resource;

class ResourceRevisionsController extends CpController
{
    use Traits\ExtractsFromModelFields;

    public function index(Request $request, Resource $resource, $model)
    {
        $model = $resource->model()
            ->where($resource->model()->qualifyColumn($resource->routeKey()), $model)
            ->first();

        $revisions = $model
            ->revisions()
            ->reverse()
            ->prepend($this->workingCopy($model))
            ->filter()
            ->each(function ($revision) use ($resource, $model) {
                $revision->attribute('item_url', cp_route('runway.revisions.show', [
                    'resource' => $resource->handle(),
                    'model' => $model->getKey(),
                    'revisionId' => $revision->id(),
                ]));
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

    public function store(Request $request, Resource $resource, $model)
    {
        $model = $resource->model()
            ->where($resource->model()->qualifyColumn($resource->routeKey()), $model)
            ->first();

        $model->createRevision([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        // todo: we might need to return more stuff from this resource, but let's see.
        return new Model($model);
    }

    public function show(Request $request, Resource $resource, $model, $revisionId)
    {
        $model = $resource->model()
            ->where($resource->model()->qualifyColumn($resource->routeKey()), $model)
            ->first();

        $revision = $model->revision($revisionId);
        $model = $model->makeFromRevision($revision);

        // TODO: Most of this is duplicated with EntriesController@edit. DRY it off.

        $blueprint = $model->runwayResource()->blueprint();

        [$values, $meta] = $this->extractFromFields($model, $resource, $blueprint);

        return [
            'title' => $model->getAttribute($resource->titleField()),
            'editing' => true,
            'actions' => [
                'save' => cp_route('runway.update', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'publish' => cp_route('runway.published.store', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'unpublish' => cp_route('runway.published.destroy', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'revisions' => cp_route('runway.revisions.index', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'restore' => cp_route('runway.restore-revision', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'createRevision' => cp_route('runway.revisions.store', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
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

    protected function workingCopy($model)
    {
        return $model->workingCopy();

//        if ($model->published()) {
//            return $model->workingCopy();
//        }

        return $model
            ->makeWorkingCopy()
            ->date($model->updated_at)
            ->user(null);
    }
}
