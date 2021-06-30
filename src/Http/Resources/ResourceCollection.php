<?php

namespace DoubleThreeDigital\Runway\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection as LaravelResourceCollection;
use Statamic\CP\Column;
use Statamic\CP\Columns;

class ResourceCollection extends LaravelResourceCollection
{
    public $collects;

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

    public function toArray($request)
    {
        $columns = $this->columns->pluck('field')->toArray();

        return [
            'data' => $this->collection->map(function ($row) use ($columns) {
                $row = $row->toArray();
                foreach ($row as $key=>$value)
                    if (!in_array($key, $columns))
                        unset($row[$key]);
                return $row;
            }),
            'meta' => [
                'columns' => $this->columns,
            ],
        ];
    }
}
