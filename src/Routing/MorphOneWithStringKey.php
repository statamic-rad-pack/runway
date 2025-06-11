<?php

namespace StatamicRadPack\Runway\Routing;

use Illuminate\Database\Eloquent\Relations\MorphOne;

class MorphOneWithStringKey extends MorphOne
{
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->getQuery()->where($this->morphType, $this->morphClass)
                ->where($this->foreignKey, (string) $this->getParentKey());
        }
    }

    public function addEagerConstraints(array $models)
    {
        $keys = array_map('strval', $this->getKeys($models, $this->localKey));

        $this->getQuery()->where($this->morphType, $this->morphClass)
            ->whereIn($this->foreignKey, $keys);
    }
}
