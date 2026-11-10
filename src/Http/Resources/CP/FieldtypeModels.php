<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Illuminate\Pagination\AbstractPaginator;
use Statamic\Fieldtypes\Relationship;

class FieldtypeModels extends Models
{
    private Relationship $fieldtype;
    public $collects = FieldtypeListedModel::class;

    public function __construct($resource, Relationship $fieldtype)
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
