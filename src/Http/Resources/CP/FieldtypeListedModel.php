<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use Statamic\Fieldtypes\Relationship;
use StatamicRadPack\Runway\Fieldtypes\RunwayModelsFieldtype;

class FieldtypeListedModel extends ListedModel
{
    private Relationship $fieldtype;

    public function fieldtype(Relationship $fieldtype): self
    {
        $this->fieldtype = $fieldtype;

        return $this;
    }

    public function toArray($request): array
    {
        $data = parent::toArray($request);

        if ($this->fieldtype instanceof RunwayModelsFieldtype) {
            $data = array_merge($data, (new FieldtypeModel($this->resource, $this->fieldtype))->resolve()['data']);
        }

        return $data;
    }
}
