<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;
use StatamicRadPack\Runway\Fieldtypes\BelongsToFieldtype;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;

class ListedModel extends JsonResource
{
    protected $blueprint;
    protected $runwayResource;
    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function runwayResource($runwayResource)
    {
        $this->runwayResource = $runwayResource;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request)
    {
        $model = $this->resource;

        // TODO: ensure relationships load properly
        return [
            'id' => $model->getKey(),
            'edit_url' => cp_route('runway.edit', ['resource' => $this->runwayResource->handle(), 'model' => $model->getRouteKey()]),
            'permalink' => $this->runwayResource->hasRouting() ? $model->uri() : null,
            'editable' => User::current()->can('edit', [$this->runwayResource, $model]),
            'viewable' => User::current()->can('view', [$this->runwayResource, $model]),
            'actions' => Action::for($model, ['resource' => $this->runwayResource->handle()]),
            $this->merge($this->values()),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            // TODO: Add comments here...
            if ($field && $field->fieldtype() instanceof BelongsToFieldtype) {
                $relationName = $this->runwayResource->eloquentRelationships()->get($key);
                $value = $this->resource->$relationName;
            } elseif (str_contains($key, '->')) {
                $value = data_get($this->resource, str_replace('->', '.', $key));
            } else {
                $value = $extra[$key] ?? $this->resource->{$key};
            }

            if (! $field) {
                return [$key => $value];
            }

            $value = $field->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        })->dd();
    }
}
