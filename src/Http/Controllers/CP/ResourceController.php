<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP;

use Illuminate\Database\Eloquent\Model;
use Statamic\CP\Breadcrumbs;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Action;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Runway\Fieldtypes\BelongsToFieldtype;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;
use StatamicRadPack\Runway\Http\Requests\CP\CreateRequest;
use StatamicRadPack\Runway\Http\Requests\CP\EditRequest;
use StatamicRadPack\Runway\Http\Requests\CP\IndexRequest;
use StatamicRadPack\Runway\Http\Requests\CP\StoreRequest;
use StatamicRadPack\Runway\Http\Requests\CP\UpdateRequest;
use StatamicRadPack\Runway\Http\Resources\CP\Model as ModelResource;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class ResourceController extends CpController
{
    use Traits\ExtractsFromModelFields, Traits\HasListingColumns, Traits\PreparesModels;

    public function index(IndexRequest $request, Resource $resource)
    {
        $listingConfig = [
            'preferencesPrefix' => "runway.{$resource->handle()}",
            'requestUrl' => cp_route('runway.listing-api', ['resource' => $resource->handle()]),
            'listingUrl' => cp_route('runway.index', ['resource' => $resource->handle()]),
        ];

        return view('runway::index', [
            'title' => $resource->name(),
            'resource' => $resource,
            'modelCount' => $resource->model()->count(),
            'primaryColumn' => $this->getPrimaryColumn($resource),
            'columns' => $resource->blueprint()->columns()
                ->setPreferred("runway.{$resource->handle()}.columns")
                ->rejectUnlisted()
                ->values(),
            'filters' => Scope::filters('runway', ['resource' => $resource->handle()]),
            'listingConfig' => $listingConfig,
            'actionUrl' => cp_route('runway.actions.run', ['resource' => $resource->handle()]),
        ]);
    }

    public function create(CreateRequest $request, Resource $resource)
    {
        $blueprint = $resource->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        $viewData = [
            'title' => __('Create :resource', ['resource' => $resource->singular()]),
            'method' => 'POST',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => $resource->plural(),
                'url' => cp_route('runway.index', [
                    'resource' => $resource->handle(),
                ]),
            ]]),
            'actions' => [
                'save' => cp_route('runway.store', ['resource' => $resource->handle()]),
            ],
            'resource' => $request->wantsJson() ? $resource->toArray() : $resource,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'permalink' => null,
            'resourceHasRoutes' => $resource->hasRouting(),
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

        $postCreatedHooks = $resource->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->fieldtype() instanceof HasManyFieldtype)
            ->map(fn (Field $field) => $field->fieldtype()->process($request->get($field->handle())))
            ->values();

        $this->prepareModelForSaving($resource, $model, $request);

        if ($resource->revisionsEnabled()) {
            $saved = $model->store([
                'message' => $request->message,
                'user' => User::current(),
            ]);
        } else {
            $saved = $model->save();
        }

        // Runs anything in the $postCreatedHooks array. See HasManyFieldtype@process for an example
        // of where this is used.
        $postCreatedHooks->each(fn ($postCreatedHook) => $postCreatedHook($resource, $model));

        return [
            'data' => (new ModelResource($model->fresh()))->resolve()['data'],
            'saved' => $saved,
        ];
    }

    public function edit(EditRequest $request, Resource $resource, $model)
    {
        $model = $resource->model()
            ->where($resource->model()->qualifyColumn($resource->routeKey()), $model)
            ->first();

        if (! $model) {
            throw new NotFoundHttpException();
        }

        $blueprint = $resource->blueprint();

        [$values, $meta] = $this->extractFromFields($model, $resource, $blueprint);

        $viewData = [
            'title' => $model->getAttribute($resource->titleField()),
            'method' => 'PATCH',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => $resource->plural(),
                'url' => cp_route('runway.index', [
                    'resource' => $resource->handle(),
                ]),
            ]]),
            'resource' => $resource,
            'actions' => [
                'save' => cp_route('runway.update', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'publish' => cp_route('runway.published.store', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'unpublish' => cp_route('runway.published.destroy', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'revisions' => cp_route('runway.revisions.index', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'restore' => cp_route('runway.restore-revision', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'createRevision' => cp_route('runway.revisions.store', ['resource' => $resource->handle(), 'model' => $model->{$resource->routeKey()}]),
                'editBlueprint' => cp_route('blueprints.edit', ['namespace' => 'runway', 'handle' => $resource->handle()]),
            ],
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $values,
            'meta' => $meta,
            'permalink' => $resource->hasRouting() ? $model->uri() : null,
            'resourceHasRoutes' => $resource->hasRouting(),
            'currentModel' => [
                'id' => $model->getKey(),
                'reference' => $model->reference(),
                'title' => $model->{$resource->titleField()},
                'edit_url' => $request->url(),
            ],
            'revisionsEnabled' => $resource->revisionsEnabled(),
            'canPublish' => true,
            'itemActions' => Action::for($model, ['resource' => $resource->handle(), 'view' => 'form']),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('runway::edit', $viewData);
    }

    public function update(UpdateRequest $request, Resource $resource, $model)
    {
        $resource->blueprint()->fields()->setParent($model)->addValues($request->all())->validator()->validate();

        $model = $resource->model()->where($resource->model()->qualifyColumn($resource->routeKey()), $model)->first();
        $model = $model->fromWorkingCopy();

        $this->prepareModelForSaving($resource, $model, $request);

        if ($request->get('from_inline_publish_form')) {
            $this->handleInlinePublishForm($resource, $model);
        }

        // statamic checks if the entry is published here, maybe we need to do that too?
        if ($resource->revisionsEnabled()) {
            $saved = $model
                ->makeWorkingCopy()
                ->user(User::current())
                ->save();

            // catch any changes through RevisionSaving event
            $model = $model->fromWorkingCopy();
        } else {
            $saved = $model->save();
        }

        [$values] = $this->extractFromFields($model, $resource, $resource->blueprint());

        return [
            'data' => array_merge((new ModelResource($model->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => $saved,
        ];
    }

    /**
     * Handle saving data from the Inline Publish Form (the one that appears when you edit models in a stack).
     */
    protected function handleInlinePublishForm(Resource $resource, Model &$model): void
    {
        collect($resource->blueprint()->fields()->all())
            ->filter(fn (Field $field) => $field->fieldtype() instanceof BelongsToFieldtype || $field->fieldtype() instanceof HasManyFieldtype)
            ->each(function (Field $field) use (&$model, $resource) {
                $relatedResource = Runway::findResource($field->get('resource'));

                $column = $relatedResource->titleField();

                $relationshipName = $resource->eloquentRelationships()->get($field->handle()) ?? $field->handle();

                $model->{$field->handle()} = $model->{$relationshipName}()
                    ->select($relatedResource->model()->qualifyColumn($relatedResource->primaryKey()), $column)
                    ->get()
                    ->each(function ($model) use ($relatedResource, $column) {
                        $model->title = $model->{$column};

                        $model->edit_url = cp_route('runway.edit', [
                            'resource' => $relatedResource->handle(),
                            'model' => $model->{$relatedResource->routeKey()},
                        ]);

                        return $model;
                    });
            });
    }
}
