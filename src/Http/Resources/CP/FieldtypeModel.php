<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Relationship;
use StatamicRadPack\Runway\Fieldtypes\RunwayModelsFieldtype;

class FieldtypeModel extends JsonResource
{
    private Relationship $fieldtype;

    public function __construct($resource, Relationship $fieldtype)
    {
        $this->fieldtype = $fieldtype;

        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $data = [
            'id' => $this->resource->getKey(),
            'reference' => $this->resource->reference(),
            'title' => $this->makeTitle($this->resource),
            'status' => $this->resource->publishedStatus(),
            'edit_url' => $this->resource->runwayEditUrl(),
        ];

        if ($this->fieldtype instanceof RunwayModelsFieldtype) {
            $data['editable'] = User::current()->can('edit', [$this->resource->runwayResource(), $this->resource]);
        }

        return ['data' => $data];
    }

    protected function makeTitle($model): ?string
    {
        if (! $titleFormat = $this->fieldtype->config('title_format')) {
            $firstListableColumn = $this->resource->runwayResource()->titleField();

            return $model->augmentedValue($firstListableColumn);
        }

        return Parse::template($titleFormat, $model->toDeferredAugmentedArray());
    }
}
