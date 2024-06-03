<?php

namespace StatamicRadPack\Runway\Http\Controllers\CP\Traits;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Section;
use StatamicRadPack\Runway\Fieldtypes\BelongsToFieldtype;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Support\Json;

trait PreparesModels
{
    protected function prepareModelForPublishForm(Resource $resource, Model $model): Collection
    {
        $blueprint = $resource->blueprint();

        // Re-casts any `object` casts to `array`.
        $model->mergeCasts(
            collect($model->getCasts())
                ->map(fn ($value) => $value === 'object' ? 'array' : $value)
                ->toArray()
        );

        return $blueprint->fields()->all()
            ->mapWithKeys(function (Field $field) use ($resource, $model) {
                $value = data_get($model, Str::replace('->', '.', $field->handle()));

                // When $value is a Carbon instance, format it with the format defined in the blueprint.
                if ($value instanceof CarbonInterface) {
                    $format = $field->get('format', 'Y-m-d H:i');

                    $value = $value->format($format);
                }

                // When $value is a JSON string, we need to decode it.
                if (Json::isJson($value)) {
                    $value = json_decode((string) $value, true);
                }

                // When the resource is a User model, handle the special role & group fields.
                if ($this->isUserResource($resource)) {
                    if ($field->handle() === 'roles') {
                        $value = DB::table(config('statamic.users.tables.role_user'))
                            ->where('user_id', $model->getKey())
                            ->pluck('role_id')
                            ->all();
                    }

                    if ($field->handle() === 'groups') {
                        $value = DB::table(config('statamic.users.tables.group_user'))
                            ->where('user_id', $model->getKey())
                            ->pluck('group_id')
                            ->all();
                    }
                }

                // HasMany fieldtype: when reordering is enabled, we need to ensure the models are returned in the correct order.
                if ($field->fieldtype() instanceof HasManyFieldtype && $field->get('reorderable', false)) {
                    $orderColumn = $field->get('order_column');

                    $value = $model->{$field->handle()}()
                        ->reorder($orderColumn, 'ASC')
                        ->get();
                }

                return [$field->handle() => $value];
            });
    }

    protected function prepareModelForSaving(Resource $resource, Model &$model, Request $request): void
    {
        $blueprint = $resource->blueprint();

        $blueprint
            ->fields()
            ->setParent($model)
            ->all()
            ->filter(fn (Field $field) => $this->shouldSaveField($field))
            ->when($resource->hasPublishStates(), function ($collection) use ($resource) {
                $collection->put($resource->publishedColumn(), new Field($resource->publishedColumn(), ['type' => 'toggle']));
            })
            ->each(function (Field $field) use ($resource, &$model, $request) {
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

                // When the resource is a User model, handle the special role & group fields.
                if ($this->isUserResource($resource)) {
                    if ($field->handle() === 'roles') {
                        DB::table(config('statamic.users.tables.role_user'))
                            ->where('user_id', $model->getKey())
                            ->delete();

                        DB::table(config('statamic.users.tables.role_user'))
                            ->insert(
                                collect($processedValue)
                                    ->map(fn ($role) => ['user_id' => $model->getKey(), 'role_id' => $role])
                                    ->all()
                            );

                        return;
                    }

                    if ($field->handle() === 'groups') {
                        DB::table(config('statamic.users.tables.group_user'))
                            ->where('user_id', $model->getKey())
                            ->delete();

                        DB::table(config('statamic.users.tables.group_user'))
                            ->insert(
                                collect($processedValue)
                                    ->map(fn ($group) => ['user_id' => $model->getKey(), 'group_id' => $group])
                                    ->all()
                            );

                        return;
                    }
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
        if ($field->fieldtype() instanceof Section) {
            return false;
        }

        if ($field->visibility() === 'computed') {
            return false;
        }

        if ($field->get('save', true) === false) {
            return false;
        }

        return true;
    }

    protected function isUserResource(Resource $resource): bool
    {
        $guard = config('statamic.users.guards.cp', 'web');
        $guardProvider = config("auth.guards.{$guard}.provider", 'users');
        $providerModel = config("auth.providers.{$guardProvider}.model", 'App\\Models\\User');

        return $providerModel === get_class($resource->model());
    }
}
