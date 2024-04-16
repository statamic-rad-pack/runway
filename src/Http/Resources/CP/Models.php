<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Statamic\CP\Column;
use Statamic\Fields\Blueprint;
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

    public function blueprint(Blueprint $blueprint): self
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function setColumnPreferenceKey(string $key): self
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    public function setColumns(): self
    {
        $columns = $this->runwayResource->blueprint()->columns()->map(function (Column $column) {
            if ($this->runwayResource->model()->hasAppended($column->field())) {
                $column->sortable(false);
            }

            return $column;
        });

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    public function toArray($request): Collection
    {
        $this->setColumns();

        return $this->collection->each(function ($model) {
            $model
                ->blueprint($this->blueprint)
                ->runwayResource($this->runwayResource)
                ->columns($this->requestedColumns());
        });
    }

    public function with($request): array
    {
        return [
            'meta' => [
                'columns' => $this->visibleColumns(),
            ],
        ];
    }
}
