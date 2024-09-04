<?php

namespace StatamicRadPack\Runway\Http\Resources\CP;

use StatamicRadPack\Runway\Fieldtypes\BaseFieldtype;

class FieldtypeListedModel extends ListedModel
{
    private BaseFieldtype $fieldtype;

    public function fieldtype(BaseFieldtype $fieldtype): self
    {
        $this->fieldtype = $fieldtype;

        return $this;
    }
}
