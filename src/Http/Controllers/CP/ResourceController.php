<?php

namespace DoubleThreeDigital\Runway\Http\Controllers\CP;

use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Http\Requests\CreateRequest;
use DoubleThreeDigital\Runway\Http\Requests\EditRequest;
use DoubleThreeDigital\Runway\Http\Requests\IndexRequest;
use DoubleThreeDigital\Runway\Http\Requests\StoreRequest;
use DoubleThreeDigital\Runway\Http\Requests\UpdateRequest;
use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Database\Eloquent\Model;
use Statamic\CP\Breadcrumbs;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Scope;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;

class ResourceController extends CpController
{
    use Traits\HasListingColumns, Traits\PreparesModels;

    public function index(IndexRequest $request, Resource $resource)
    {
        $blueprint = $resource->blueprint();

        $listingConfig = [
            'preferencesPrefix' => "runway.{$resource->handle()}",
            'requestUrl' => cp_route('runway.listing-api', ['resource' => $resource->handle()]),
            'listingUrl' => cp_route('runway.index', ['resource' => $resource->handle()]),
        ];

        $columns = $this->buildColumns($resource, $blueprint);

        return view('runway::index', [
            'title' => $resource->name(),
            'resource' => $resource,
            'recordCount' => $resource->model()->count(),
            'primaryColumn' => $this->getPrimaryColumn($resource),
            'columns' => $resource->blueprint()->columns()
                ->filter(fn ($column) => in_array($column->field, collect($columns)->pluck('handle')->toArray()))
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
            'action' => cp_route('runway.store', ['resource' => $resource->handle()]),
            'method' => 'POST',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => $resource->plural(),
                'url' => cp_route('runway.index', [
                    'resource' => $resource->handle(),
                ]),
            ]]),
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

        $model->save();

        // Runs anything in the $postCreatedHooks array. See HasManyFieldtype@process for an example
        // of where this is used.
        $postCreatedHooks->each(fn ($postCreatedHook) => $postCreatedHook($resource, $model));

        return [
            'data' => $this->getReturnData($resource, $model),
            'redirect' => cp_route('runway.edit', [
                'resource' => $resource->handle(),
                'record' => $model->{$resource->routeKey()},
            ]),
        ];
    }

    public function edit(EditRequest $request, Resource $resource, $record)
    {
        $record = $resource->model()
            ->where($resource->model()->qualifyColumn($resource->routeKey()), $record)
            ->first();

        if (! $record) {
            throw new NotFoundHttpException();
        }

        $values = $this->prepareModelForPublishForm($resource, $record);

        $blueprint = $resource->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        $viewData = [
            'title' => __('Edit :resource', ['resource' => $resource->singular()]),
            'action' => cp_route('runway.update', [
                'resource' => $resource->handle(),
                'record' => $record->{$resource->routeKey()},
            ]),
            'method' => 'PATCH',
            'breadcrumbs' => new Breadcrumbs([[
                'text' => $resource->plural(),
                'url' => cp_route('runway.index', [
                    'resource' => $resource->handle(),
                ]),
            ]]),
            'resource' => $resource,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'permalink' => $resource->hasRouting() ? $record->uri() : null,
            'resourceHasRoutes' => $resource->hasRouting(),
            'currentRecord' => [
                'id' => $record->getKey(),
                'title' => $record->{$resource->titleField()},
                'edit_url' => $request->url(),
            ],
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('runway::edit', $viewData);
    }

    public function update(UpdateRequest $request, Resource $resource, $record)
    {
        $resource->blueprint()->fields()->addValues($request->all())->validator()->validate();

        $model = $resource->model()->where($resource->model()->qualifyColumn($resource->routeKey()), $record)->first();

        $this->prepareModelForSaving($resource, $model, $request);

        $model->save();

        if ($request->get('from_inline_publish_form')) {
            $this->handleInlinePublishForm($resource, $model);
        }

        return ['data' => $this->getReturnData($resource, $model)];
    }

    /**
     * Build an array with the correct return data for the inline publish forms.
     */
    protected function getReturnData(Resource $resource, Model $record): array
    {
        return array_merge($record->toArray(), [
            'title' => $record->{$resource->titleField()},
            'edit_url' => cp_route('runway.edit', [
                'resource' => $resource->handle(),
                'record' => $record->{$resource->routeKey()},
            ]),
        ]);
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
                            'record' => $model->{$relatedResource->routeKey()},
                        ]);

                        return $model;
                    });
            });
    }
}
