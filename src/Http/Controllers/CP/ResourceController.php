<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Support\Facades\DB;
use Statamic\CP\Column;
use Statamic\CP\Navigation\NavItem;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Action;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Runway\Http\Requests\CP\CreateRequest;
use StatamicRadPack\Runway\Http\Requests\CP\EditRequest;
use StatamicRadPack\Runway\Http\Requests\CP\IndexRequest;
use StatamicRadPack\Runway\Http\Requests\CP\StoreRequest;
use StatamicRadPack\Runway\Http\Requests\CP\UpdateRequest;
use StatamicRadPack\Runway\Http\Resources\CP\Model as ModelResource;
use StatamicRadPack\Runway\Relationships;
use StatamicRadPack\Runway\Resource;

class ResourceController extends CpController
{
    use Traits\ExtractsFromModelFields, Traits\HasListingColumns, Traits\PreparesModels;

    public function index(IndexRequest $request, Resource $resource)
    {
        $columns = $resource->blueprint()->columns()
            ->when($resource->hasPublishStates(), function ($collection) {
                $collection->put('status', Column::make('status')
                    ->listable(true)
                    ->visible(true)
                    ->defaultVisibility(true)
                    ->sortable(false));
            })
            ->setPreferred("runway.{$resource->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        return view('runway::index', [
            'icon' => $this->getResourceIcon($resource),
            'resource' => $resource,
            'columns' => $columns,
            'filters' => Scope::filters('runway', ['resource' => $resource->handle()]),
            'canCreate' => User::current()->can('create', $resource)
                && $resource->hasVisibleBlueprint()
                && ! $resource->readOnly(),
            'canEditBlueprint' => User::current()->can('configure fields'),
            'hasPublishStates' => $resource->hasPublishStates(),
            'actions' => Action::for($resource, ['view' => 'form']),
            'titleColumn' => $this->getTitleColumn($resource),
        ]);
    }

    public function create(CreateRequest $request, Resource $resource)
    {
        $blueprint = $resource->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        $values = $fields->values()->merge([
            $resource->publishedColumn() => true,
        ]);

        $viewData = [
            'title' => __('Create :resource', ['resource' => $resource->singular()]),
            'method' => 'post',
            'resource' => $request->wantsJson() ? $resource->toArray() : $resource,
            'actions' => [
                'save' => cp_route('runway.store', ['resource' => $resource->handle()]),
            ],
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'resourceHasRoutes' => $resource->hasRouting(),
            'canManagePublishState' => User::current()->can('publish', $resource),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('runway::create', $viewData);
    }

    public function store(StoreRequest $request, Resource $resource)
    {
        $resource
            ->blueprint()
            ->fields()
            ->addValues($request->all())
            ->validator()
            ->validate();

        $model = $resource->model();

        $this->prepareModelForSaving($resource, $model, $request);

        if ($resource->revisionsEnabled()) {
            $saved = $model->store([
                'message' => $request->message,
                'user' => User::current(),
            ]);
        } else {
            $saved = DB::transaction(function () use ($model, $request) {
                $model->save();
                Relationships::for($model)->with($request->all())->save();

                return true;
            });
        }

        return [
            'data' => (new ModelResource($model->fresh()))->resolve()['data'],
            'saved' => $saved,
        ];
    }

    public function edit(EditRequest $request, Resource $resource, $model)
    {
        $model = $resource->newEloquentQuery()->firstWhere($resource->model()->qualifyColumn($resource->routeKey()), $model);

        if (! $model) {
            throw new NotFoundHttpException;
        }

        $model = $model->fromWorkingCopy();

        $blueprint = $resource->blueprint();

        [$values, $meta] = $this->extractFromFields($model, $resource, $blueprint);

        $viewData = [
            'title' => $model->getAttribute($resource->titleField()),
            'reference' => $model->reference(),
            'method' => 'patch',
            'resource' => $request->wantsJson() ? $resource->toArray() : $resource,
            'actions' => [
                'save' => $model->runwayUpdateUrl(),
                'publish' => $model->runwayPublishUrl(),
                'unpublish' => $model->runwayUnpublishUrl(),
                'revisions' => $model->runwayRevisionsUrl(),
                'restore' => $model->runwayRestoreRevisionUrl(),
                'createRevision' => $model->runwayCreateRevisionUrl(),
                'editBlueprint' => cp_route('blueprints.edit', ['namespace' => 'runway', 'handle' => $resource->handle()]),
            ],
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $values,
            'meta' => $meta,
            'readOnly' => $resource->readOnly(),
            'status' => $model->publishedStatus(),
            'permalink' => $resource->hasRouting() ? $model->uri() : null,
            'resourceHasRoutes' => $resource->hasRouting(),
            'canManagePublishState' => User::current()->can('publish', $resource),
            'canEditBlueprint' => User::current()->can('configure fields'),
            'itemActions' => Action::for($model, ['resource' => $resource->handle(), 'view' => 'form']),
            'revisionsEnabled' => $resource->revisionsEnabled(),
            'hasWorkingCopy' => $model->hasWorkingCopy(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('runway::edit', $viewData);
    }

    public function update(UpdateRequest $request, Resource $resource, $model)
    {
        $resource->blueprint()->fields()->setParent($model)->addValues($request->all())->validator()->validate();

        $model = $resource->newEloquentQuery()->firstWhere($resource->model()->qualifyColumn($resource->routeKey()), $model);
        $model = $model->fromWorkingCopy();

        $this->prepareModelForSaving($resource, $model, $request);

        if ($resource->revisionsEnabled() && $model->published()) {
            $saved = $model
                ->makeWorkingCopy()
                ->user(User::current())
                ->save();

            $model = $model->fromWorkingCopy();
        } else {
            $saved = DB::transaction(function () use ($model, $request) {
                $model->save();
                Relationships::for($model)->with($request->all())->save();

                return true;
            });

            $model->refresh();
        }

        [$values] = $this->extractFromFields($model, $resource, $resource->blueprint());

        return [
            'data' => array_merge((new ModelResource($model))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => $saved,
        ];
    }

    private function getResourceIcon(Resource $resource): string
    {
        return Nav::build()->pluck('items')->flatten()
            ->filter(fn (NavItem $navItem) => $navItem->url() === cp_route('runway.index', ['resource' => $resource->handle()]))
            ->first()
            ?->icon();
    }
}
