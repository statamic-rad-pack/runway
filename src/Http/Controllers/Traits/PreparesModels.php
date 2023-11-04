<?php

namespace DoubleThreeDigital\Runway\Http\Controllers\Traits;

use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Fields\Field;

trait PreparesModels
{
    protected function prepareModelForSaving(Resource $resource, Model &$model, Request $request)
    {
        $blueprint = $resource->blueprint();

        $blueprint->fields()->all()
            ->filter(fn (Field $field) => $this->shouldSaveField($field))
            ->each(function (Field $field) use (&$model, $request) {
                $processedValue = $field->fieldtype()->process($request->get($field->handle()));

                if ($field->fieldtype() instanceof HasManyFieldtype) {
                    return;
                }

                // Skip the field if it exists in the model's $appends array AND there's no mutator for it on the model.
                if (in_array($field->handle(), $model->getAppends(), true) && ! $model->hasSetMutator($field->handle()) && ! $model->hasAttributeSetMutator($field->handle())) {
                    return;
                }

                // If it's a BelongsTo field and the $processedValue is an array, then we want the first item in the array.
                if ($field->fieldtype() instanceof BelongsToFieldtype && is_array($processedValue)) {
                    $processedValue = Arr::first($processedValue);
                }

                // When $processedValue is null and there's no cast set on the model, we should JSON encode it.
                if (
                    is_array($processedValue)
                    && ! str_contains($field->handle(), '->')
                    && ! $model->hasCast($field->handle(), ['json', 'array', 'collection', 'object', 'encrypted:array', 'encrypted:collection', 'encrypted:object'])
                ) {
                    $processedValue = json_encode($processedValue, JSON_THROW_ON_ERROR);
                }

                $model->setAttribute($field->handle(), $processedValue);
            });
    }

    protected function shouldSaveField(Field $field): bool
    {
        $config = $field->config();

        if ($field->type() === 'section') {
            return false;
        }

        if ($field->visibility() === 'computed') {
            return false;
        }

        if (isset($config['save']) && $config['save'] === false) {
            return false;
        }

        return true;
    }
}
