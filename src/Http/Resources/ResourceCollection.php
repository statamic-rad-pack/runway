<?php

namespace DuncanMcClean\Runway\Http\Resources;

use DuncanMcClean\Runway\Fieldtypes\BelongsToFieldtype;
use DuncanMcClean\Runway\Fieldtypes\HasManyFieldtype;
use DuncanMcClean\Runway\Runway;
use Illuminate\Http\Resources\Json\ResourceCollection as LaravelResourceCollection;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ResourceCollection extends LaravelResourceCollection
{
    public $collects;

    public $columns;

    protected $resourceHandle;

    protected $runwayResource;

    protected $columnPreferenceKey;

    public function setColumnPreferenceKey($key)
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    public function setColumns($originalColumns)
    {
        $columns = $this->runwayResource->blueprint()->columns()
            ->filter(fn ($column) => in_array($column->field, collect($originalColumns)->pluck('handle')->toArray()));

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    public function setResourceHandle($handle)
    {
        $this->runwayResource = Runway::findResource($handle);
        $this->resourceHandle = $handle;

        return $this;
    }

    public function toArray($request)
    {
        $columns = $this->columns->pluck('field')->toArray();
        $handle = $this->resourceHandle;

        return [
            'data' => $this->collection->map(function ($record) use ($columns, $handle) {
                $row = $record->toArray();

                foreach ($row as $key => $value) {
                    if (! in_array($key, $columns)) {
                        unset($row[$key]);
                    }

                    if ($this->runwayResource->blueprint()->hasField($key)) {
                        // If we've eager loaded in relationships, just pass in the model
                        // instance. We can prevent extra queries this way.
                        if ($this->runwayResource->blueprint()->field($key)->fieldtype() instanceof BelongsToFieldtype) {
                            $relationName = $this->runwayResource->eagerLoadingRelations()->get($key);

                            if ($record->relationLoaded($relationName)) {
                                $value = $record->$relationName;
                            }
                        }

                        if ($this->runwayResource->blueprint()->field($key)->fieldtype() instanceof HasManyFieldtype) {
                            $relationName = $key;

                            if ($record->relationLoaded($relationName)) {
                                $value = $record->$relationName;
                            }
                        }

                        $row[$key] = $this->runwayResource->blueprint()->field($key)->setValue($value)->preProcessIndex()->value();
                    }
                }

                foreach ($this->runwayResource->blueprint()->fields()->except(array_keys($row))->all() as $key => $field) {
                    $row[$key] = $field->setValue($record->{$key})->preProcessIndex()->value();
                }

                $row['id'] = $record->getKey();
                $row['edit_url'] = cp_route('runway.edit', ['resourceHandle' => $handle, 'record' => $record->getRouteKey()]);
                $row['permalink'] = $this->runwayResource->hasRouting() ? $record->uri() : null;
                $row['editable'] = User::current()->can('edit', $this->runwayResource);
                $row['viewable'] = User::current()->can('view', $this->runwayResource);
                $row['actions'] = Action::for($record, ['resource' => $this->runwayResource->handle()]);

                return $row;
            }),
            'meta' => [
                'columns' => $this->columns,
            ],
        ];
    }
}
