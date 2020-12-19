<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Http\Requests\StoreRequest;
use DoubleThreeDigital\Runway\Http\Requests\UpdateRequest;
use DoubleThreeDigital\Runway\Support\ModelFinder;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class ModelController extends CpController
{
    public function index(Request $request, $model)
    {
        $model = ModelFinder::find($model);
        $blueprint = $model['blueprint'];

        if (! $request->user()->hasPermission("View {$model['_handle']}") && ! $request->user()->isSuper()) {
            abort('403');
        }

        $query = (new $model['model']())
            ->orderBy($model['listing_sort']['column'], $model['listing_sort']['direction']);

        if ($searchQuery = $request->input('query')) {
            $query->where(function ($query) use ($searchQuery, $blueprint) {
                $wildcard = '%'.$searchQuery.'%';

                foreach ($blueprint->fields()->items()->toArray() as $field) {
                    $query->orWhere($field['handle'], 'LIKE', $wildcard);
                }
            });
        }

        $columns = collect($model['listing_columns'])
            ->map(function ($columnKey) use ($model, $blueprint) {
                $field = $blueprint->field($columnKey);

                return [
                    'handle' => $columnKey,
                    'title'  => $field->display(),
                    'has_link' => $model['listing_columns'][0] === $columnKey,
                ];
            })
            ->toArray();

        return view('runway::index', [
            'title'     => $model['name'],
            'model'     => $model,
            'records'   => $query->paginate(config('statamic.cp.pagination_size')),
            'columns'   => $columns,
        ]);
    }

    public function create(Request $request, $model)
    {
        $model = ModelFinder::find($model);

        if (! $request->user()->hasPermission("Create new {$model['_handle']}") && ! $request->user()->isSuper()) {
            abort('403');
        }

        $blueprint = $model['blueprint'];
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('runway::create', [
            'model'     => $model,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'action'    => cp_route('runway.store', ['model' => $model['_handle']]),
        ]);
    }

    public function store(StoreRequest $request, $model)
    {
        $model = ModelFinder::find($model);
        $record = (new $model['model']());

        if (! $request->user()->hasPermission("Create new {$model['_handle']}") && ! $request->user()->isSuper()) {
            abort('403');
        }

        foreach ($model['blueprint']->fields()->all() as $fieldKey => $field) {
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
                'model'     => $model['_handle'],
                'record'    => $record->{$model['primary_key']},
            ]),
        ];
    }

    public function edit(Request $request, $model, $record)
    {
        $model = ModelFinder::find($model);
        $record = (new $model['model']())->find($record);

        if (! $request->user()->hasPermission("Edit {$model['_handle']}") && ! $request->user()->isSuper()) {
            abort('403');
        }

        $values = [];
        $blueprintFieldKeys = $model['blueprint']->fields()->all()->keys()->toArray();

        foreach ($blueprintFieldKeys as $fieldKey) {
            $value = $record->{$fieldKey};

            if ($value instanceof \Carbon\Carbon) {
                $value = $value->format('Y-m-d H:i');
            }

            $values[$fieldKey] = $value;
        }

        $blueprint = $model['blueprint'];
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return view('runway::edit', [
            'model'     => $model,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'action'    => cp_route('runway.update', [
                'model'     => $model['_handle'],
                'record'    => $record->{$model['primary_key']},
            ]),
        ]);
    }

    public function update(UpdateRequest $request, $model, $record)
    {
        $model = ModelFinder::find($model);
        $record = (new $model['model']())->find($record);

        if (! $request->user()->hasPermission("Edit {$model['_handle']}") && ! $request->user()->isSuper()) {
            abort('403');
        }

        foreach ($model['blueprint']->fields()->all() as $fieldKey => $field) {
            $processedValue = $field->fieldtype()->process($request->get($fieldKey));

            if (is_array($processedValue)) {
                $processedValue = json_encode($processedValue);
            }

            $record->{$fieldKey} = $processedValue;
        }

        $record->save();

        return [
            'record'    => $record->toArray(),
        ];
    }

    public function destroy(Request $request, $model, $record)
    {
        $model = ModelFinder::find($model);
        $record = (new $model['model']())->find($record);

        if (! $request->user()->hasPermission("Delete {$model['_handle']}") && ! $request->user()->isSuper()) {
            abort('403');
        }

        $record->delete();

        return redirect(cp_route('runway.index', [
            'model' => $model['_handle'],
        ]))->with('success', "{$model['singular']} deleted");
    }
}
