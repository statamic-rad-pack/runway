<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use StatamicRadPack\Runway\Fieldtypes\BelongsToFieldtype;
use StatamicRadPack\Runway\Resource;

class ListedModel extends JsonResource
{
    protected $blueprint;
    protected $runwayResource;
    protected $columns;

    public function blueprint(Blueprint $blueprint): self
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function runwayResource(Resource $runwayResource): self
    {
        $this->runwayResource = $runwayResource;

        return $this;
    }

    public function columns($columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request): array
    {
        $model = $this->resource;

        return [
            'id' => $model->getKey(),
            'title' => $model->getAttribute($this->runwayResource->titleField()),
            'published' => $this->resource->published(),
            'status' => $this->resource->publishedStatus(),
            'edit_url' => $model->runwayEditUrl(),
            'permalink' => $this->runwayResource->hasRouting() ? $model->uri() : null,
            'editable' => User::current()->can('edit', [$this->runwayResource, $model]),
            'viewable' => User::current()->can('view', [$this->runwayResource, $model]),
            'collection' => ['dated' => false],
            $this->merge($this->values()),
        ];
    }

    protected function values($extra = []): Collection
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            // When it's a Belongs To field, the field handle won't be the relationship name.
            // We need to resolve it to the relationship name to get the value from the model.
            if ($field && $field->fieldtype() instanceof BelongsToFieldtype) {
                $relationName = $this->runwayResource->eloquentRelationships()->get($key);
                $value = $this->resource->$relationName;
            }
            // When it's a nested field, we need to get the value from the nested JSON object, using data_get().
            elseif ($field && $nestedFieldPrefix = $this->runwayResource->nestedFieldPrefix($field->handle())) {
                $fieldKey = Str::after($field->handle(), "{$nestedFieldPrefix}_");
                $value = data_get($this->resource, "{$nestedFieldPrefix}.{$fieldKey}");
            } else {
                $value = $extra[$key] ?? $this->resource->getAttribute($key);
            }

            if (! $field) {
                return [$key => $value];
            }

            $value = $field->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
