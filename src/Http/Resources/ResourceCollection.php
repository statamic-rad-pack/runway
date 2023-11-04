<?php

namespace DoubleThreeDigital\Runway\Http\Resources;

use DoubleThreeDigital\Runway\Fieldtypes\BelongsToFieldtype;
use DoubleThreeDigital\Runway\Fieldtypes\HasManyFieldtype;
use DoubleThreeDigital\Runway\Runway;
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

    public function setColumnPreferenceKey($key): self
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    public function setColumns($originalColumns): self
    {
        $columns = $this->runwayResource->blueprint()->columns()
            ->filter(fn ($column) => in_array($column->field, collect($originalColumns)->pluck('handle')->toArray()));

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    public function setResourceHandle($handle): self
    {
        $this->runwayResource = Runway::findResource($handle);
        $this->resourceHandle = $handle;

        return $this;
    }

    public function toArray($request): array
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
                            $relationName = $this->runwayResource->eloquentRelationships()->get($key);

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

                foreach ($this->runwayResource->blueprint()->fields()->except(array_keys($row))->all() as $fieldHandle => $field) {
                    $key = str_replace('->', '.', $fieldHandle);

                    $row[$fieldHandle] = $field->setValue(data_get($record, $key))->preProcessIndex()->value();
                }

                $row['id'] = $record->getKey();
                $row['edit_url'] = cp_route('runway.edit', ['resource' => $handle, 'record' => $record->getRouteKey()]);
                $row['permalink'] = $this->runwayResource->hasRouting() ? $record->uri() : null;
                $row['editable'] = User::current()->can('edit', $this->runwayResource);
                $row['viewable'] = User::current()->can('view', $this->runwayResource);
                $row['actions'] = Action::for($record, ['resource' => $this->runwayResource->handle()]);

                return $row;
            }),
            'meta' => ['columns' => $this->columns],
        ];
    }
}
