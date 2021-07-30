<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Http\Requests\CreateRequest;
use DoubleThreeDigital\Runway\Http\Requests\EditRequest;
use DoubleThreeDigital\Runway\Http\Requests\IndexRequest;
use DoubleThreeDigital\Runway\Http\Requests\StoreRequest;
use DoubleThreeDigital\Runway\Http\Requests\UpdateRequest;
use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Support\Json;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Scope;
use Statamic\Http\Controllers\CP\CpController;

class ResourceController extends CpController
{
    public function index(IndexRequest $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);
        $blueprint = $resource->blueprint();

        $listingConfig = [
            'preferencesPrefix' => "runway.{$resource->handle()}",
            'requestUrl'        => cp_route('runway.listing-api', ['resourceHandle' => $resource->handle()]),
            'listingUrl'        => cp_route('runway.index', ['resourceHandle' => $resource->handle()]),
        ];

        return view('runway::index', [
            'title'         => $resource->name(),
            'resource'      => $resource,
            'recordCount'   => $resource->model()->count(),
            'columns'       => $this->buildColumns($resource, $blueprint),
            'filters'       => Scope::filters("runway{$resourceHandle}"),
            'listingConfig' => $listingConfig,
            'actionUrl'     => cp_route('runway.actions.run', ['resourceHandle' => $resourceHandle]),
        ]);
    }

    public function create(CreateRequest $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);

        $blueprint = $resource->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('runway::create', [
            'breadcrumbs' => new Breadcrumbs([
                [
                    'text' => $resource->plural(),
                    'url' => cp_route('runway.index', [
                        'resourceHandle' => $resource->handle(),
                    ]),
                ],
            ]),
            'resource'  => $resource,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'action'    => cp_route('runway.store', ['resourceHandle' => $resource->handle()]),
        ]);
    }

    public function store(StoreRequest $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model();

        foreach ($resource->blueprint()->fields()->all() as $fieldKey => $field) {
            if ($field->type() === 'section') {
                continue;
            }

            $processedValue = $field->fieldtype()->process($request->get($fieldKey));

            if (is_array($processedValue)) {
                $processedValue = json_encode($processedValue);
            }

            $record->{$fieldKey} = $processedValue;
        }

        $record->save();

        return [
            'redirect'  => cp_route('runway.edit', [
                'resourceHandle'  => $resource->handle(),
                'record' => $record->{$resource->primaryKey()},
            ]),
        ];
    }

    public function edit(EditRequest $request, $resourceHandle, $record)
    {
        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->where($resource->routeKey(), $record)->first();

        $values = [];
        $blueprintFieldKeys = $resource->blueprint()->fields()->all()->keys()->toArray();

        foreach ($blueprintFieldKeys as $fieldKey) {
            $value = $record->{$fieldKey};

            if ($value instanceof \Carbon\Carbon) {
                $format = $defaultFormat = 'Y-m-d H:i';

                if ($field = $resource->blueprint()->field($fieldKey)) {
                    $format = $field->get('format', $defaultFormat);
                }

                $value = $value->format($format);
            }

            if (Json::isJson($value)) {
                $value = json_decode($value, true);
            }

            $values[$fieldKey] = $value;
        }

        $blueprint = $resource->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return view('runway::edit', [
            'breadcrumbs' => new Breadcrumbs([
                [
                    'text' => $resource->plural(),
                    'url' => cp_route('runway.index', [
                        'resourceHandle' => $resource->handle(),
                    ]),
                ],
            ]),
            'resource'  => $resource,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'action'    => cp_route('runway.update', [
                'resourceHandle'  => $resource->handle(),
                'record' => $record->{$resource->routeKey()},
            ]),
            'permalink' => $resource->hasRouting()
                ? $record->uri()
                : null,
        ]);
    }

    public function update(UpdateRequest $request, $resourceHandle, $record)
    {
        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->where($resource->routeKey(), $record)->first();

        foreach ($resource->blueprint()->fields()->all() as $fieldKey => $field) {
            if ($field->type() === 'section') {
                continue;
            }

            $processedValue = $field->fieldtype()->process($request->get($fieldKey));

            if (is_array($processedValue)) {
                $processedValue = json_encode($processedValue);
            }

            $record->{$fieldKey} = $processedValue;
        }

        $record->save();

        return [
            'record' => $record->toArray(),
            'resource_handle' => $resource->handle(),
        ];
    }

    public function destroy(Request $request, $resourceHandle, $record)
    {
        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->where($resource->routeKey(), $record)->first();

        $record->delete();

        return true;
    }

    /**
     * This method is a duplicate of code in the `ResourceListingController`.
     * Update both if you make any changes.
     */
    protected function buildColumns($resource, $blueprint)
    {
        return collect($resource->listableColumns())
            ->map(function ($columnKey) use ($resource, $blueprint) {
                $field = $blueprint->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title'  => !$field ? $columnKey : $field->display(),
                    'has_link' => $resource->listableColumns()[0] === $columnKey,
                ];
            })
            ->toArray();
    }
}
