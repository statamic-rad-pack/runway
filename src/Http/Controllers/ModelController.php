<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use DoubleThreeDigital\Runway\Support\ModelFinder;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class ModelController extends CpController
{
    public function index(Request $request, $model)
    {
        $model = ModelFinder::find($model);

        $query = (new $model['model']())->query();

        return view('runway::index', [
            'title'     => $model['name'],
            'model'     => $model,
            'records'   => $query->paginate(config('statamic.cp.pagination_size')),
        ]);
    }

    public function create(Request $request, $model)
    {
        $model = ModelFinder::find($model);

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

    public function store(Request $request, $model)
    {
        $model = ModelFinder::find($model);

        $this->validate($request, $model['blueprint']->fields()->validator()->rules());

        $record = (new $model['model']());

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
                'record'    => $record->id
            ]),
        ];
    }

    public function edit(Request $request, $model, $record)
    {
        $model = ModelFinder::find($model);
        $record = (new $model['model']())->find($record);

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
                'record'    => $record->id,
            ]),
        ]);
    }

    public function update(Request $request, $model, $record)
    {
        $model = ModelFinder::find($model);
        $record = (new $model['model']())->find($record);

        $this->validate($request, $model['blueprint']->fields()->validator()->rules());

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

        $record->delete();

        return redirect(route('runway.index', [
            'model' => $model['_handle'],
        ]));
    }
}
