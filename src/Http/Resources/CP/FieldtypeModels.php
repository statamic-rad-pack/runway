<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Pagination\AbstractPaginator;
use StatamicRadPack\Runway\Fieldtypes\BaseFieldtype;

class FieldtypeModels extends Models
{
    private BaseFieldtype $fieldtype;
    public $collects = FieldtypeListedModel::class;

    public function __construct($resource, BaseFieldtype $fieldtype)
    {
        $this->fieldtype = $fieldtype;

        parent::__construct($resource);
    }

    protected function collectResource($resource)
    {
        $collection = parent::collectResource($resource);

        if ($collection instanceof AbstractPaginator) {
            $collection->getCollection()->each->fieldtype($this->fieldtype);
        } else {
            $collection->each->fieldtype($this->fieldtype);
        }

        return $collection;
    }
}
