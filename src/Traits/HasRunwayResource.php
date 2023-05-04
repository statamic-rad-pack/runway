<?php

namespace DoubleThreeDigital\Runway\Traits;

use DoubleThreeDigital\Runway\Data\AugmentedModel;
use DoubleThreeDigital\Runway\Data\HasAugmentedInstance;
use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Statamic\Contracts\Data\Augmented;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Support\Traits\FluentlyGetsAndSets;

trait HasRunwayResource
{
    use HasAugmentedInstance, FluentlyGetsAndSets;
    use ResolvesValues {
        resolveGqlValue as traitResolveGqlValue;
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedModel($this);
    }

    public function augmentedValue($key)
    {
        return $this->augmented()->get($key);
    }

    public function runwayResource(): Resource
    {
        return Runway::findResourceByModel($this);
    }
}
