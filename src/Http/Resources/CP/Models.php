<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;
use StatamicRadPack\Runway\Resource;

class Models extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ListedModel::class;
    protected $runwayResource;
    protected $blueprint;
    protected $columns;
    protected $columnPreferenceKey;

    public function runwayResource(Resource $resource): self
    {
        $this->runwayResource = $resource;

        return $this;
    }

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function setColumnPreferenceKey($key): self
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    public function setColumns(): self
    {
        $columns = $this->runwayResource->blueprint()->columns();

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    public function toArray($request)
    {
        $this->setColumns();

        return $this->collection->each(function ($model) {
            $model
                ->blueprint($this->blueprint)
                ->runwayResource($this->runwayResource)
                ->columns($this->requestedColumns());
        });
    }

    public function with($request)
    {
        return [
            'meta' => [
                'columns' => $this->visibleColumns(),
            ],
        ];
    }
}
