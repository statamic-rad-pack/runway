<?php

namespace DoubleThreeDigital\Runway\Http\Resources;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Http\Resources\Json\ResourceCollection as LaravelResourceCollection;
use Statamic\CP\Column;
use Statamic\CP\Columns;
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

    public function setColumns($columns)
    {
        $listingColumns = [];

        foreach ($columns as $column) {
            $listingColumns[] = Column::make($column['handle'])->label($column['title']);
        }

        $columns = new Columns($listingColumns);

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
                }

                $row['_id'] = $record->getKey();
                $row['edit_url'] = cp_route('runway.edit', ['resourceHandle' => $handle, 'record' => $record->getKey()]);
                $row['delete_url'] = cp_route('runway.destroy', ['resourceHandle' => $handle, 'record' => $record->getKey()]);
                $row['permalink'] = $this->runwayResource->hasRouting() ? $record->uri() : null;
                $row['editable'] = User::current()->hasPermission("Edit {$this->runwayResource->plural()}") || User::current()->isSuper();
                $row['viewable'] = User::current()->hasPermission("View {$this->runwayResource->plural()}") || User::current()->isSuper();
                $row['actions'] = Action::for($record, ['resource' => $this->runwayResource->handle()]);

                return $row;
            }),
            'meta' => [
                'columns' => $this->columns,
            ],
        ];
    }
}
