<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Parse;
use StatamicRadPack\Runway\Fieldtypes\BaseFieldtype;

class FieldtypeModel extends JsonResource
{
    private BaseFieldtype $fieldtype;

    public function __construct($resource, BaseFieldtype $fieldtype)
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

        return ['data' => $data];
    }

    protected function makeTitle($model): ?string
    {
        if (! $titleFormat = $this->fieldtype->config('title_format')) {
            $firstListableColumn = $this->resource->runwayResource()->titleField();

            return $model->augmentedValue($firstListableColumn);
        }

        return Parse::template($titleFormat, $model->toAugmentedArray());
    }
}
