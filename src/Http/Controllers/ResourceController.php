<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Http\Requests\StoreRequest;
use DoubleThreeDigital\Runway\Http\Requests\UpdateRequest;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class ResourceController extends CpController
{
    // TODO: need to put requests in place for authorization and validation

    public function index(Request $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);
        $blueprint = $resource->blueprint();

        if (! User::current()->hasPermission("View {$resource->plural()}") && ! User::current()->isSuper()) {
            abort('403');
        }

        $query = $resource->model()
            ->orderBy($resource->listingSort()['column'], $resource->listingSort()['direction']);

        if ($searchQuery = $request->input('query')) {
            $query->where(function ($query) use ($searchQuery, $blueprint) {
                $wildcard = '%'.$searchQuery.'%';

                foreach ($blueprint->fields()->items()->toArray() as $field) {
                    $query->orWhere($field['handle'], 'LIKE', $wildcard);
                }
            });
        }

        $columns = collect($resource->listingColumns())
            ->map(function ($columnKey) use ($resource, $blueprint) {
                $field = $blueprint->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title'  => !$field ? $columnKey : $field->display(),
                    'has_link' => $resource->listingColumns()[0] === $columnKey,
                ];
            })
            ->toArray();

        return view('runway::index', [
            'title'    => $resource->name(),
            'resource' => $resource,
            'records'  => $query->paginate(config('statamic.cp.pagination_size')),
            'columns'  => $columns,
        ]);
    }

    public function create(Request $request, $resourceHandle)
    {
        $resource = Runway::findResource($resourceHandle);

        if (! User::current()->hasPermission("Create new {$resource->plural()}") && ! User::current()->isSuper()) {
            abort('403');
        }

        $blueprint = $resource->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('runway::create', [
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

        if (! User::current()->hasPermission("Create new {$resource->plural()}") && ! User::current()->isSuper()) {
            abort('403');
        }

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
            'record'    => $record->toArray(),
            'redirect'  => cp_route('runway.edit', [
                'resourceHandle'  => $resource->handle(),
                'record' => $record->{$resource->primaryKey()},
            ]),
        ];
    }

    public function edit(Request $request, $resourceHandle, $record)
    {
        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->where($resource->routeKey(), $record)->first();

        if (! User::current()->hasPermission("Edit {$resource->singular()}") && ! User::current()->isSuper()) {
            abort('403');
        }

        $values = [];
        $blueprintFieldKeys = $resource->blueprint()->fields()->all()->keys()->toArray();

        foreach ($blueprintFieldKeys as $fieldKey) {
            $value = $record->{$fieldKey};

            if ($value instanceof \Carbon\Carbon) {
                $value = $value->format('Y-m-d H:i');
            }

            $values[$fieldKey] = $value;
        }

        $blueprint = $resource->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return view('runway::edit', [
            'resource'  => $resource,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'action'    => cp_route('runway.update', [
                'resourceHandle'  => $resource->handle(),
                'record' => $record->{$resource->primaryKey()},
            ]),
        ]);
    }

    public function update(UpdateRequest $request, $resourceHandle, $record)
    {
        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->where($resource->routeKey(), $record)->first();

        if (! User::current()->hasPermission("Edit {$resource->singular()}") && ! User::current()->isSuper()) {
            abort('403');
        }

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
        ];
    }

    public function destroy(Request $request, $resourceHandle, $record)
    {
        $resource = Runway::findResource($resourceHandle);
        $record = $resource->model()->where($resource->routeKey(), $record)->first();

        if (! User::current()->hasPermission("Delete {$resource->singular()}") && ! User::current()->isSuper()) {
            abort('403');
        }

        $record->delete();

        return redirect(cp_route('runway.index', [
            'resourceHandle' => $resource->handle(),
        ]))->with('success', "{$resource->singular()} deleted");
    }
}
