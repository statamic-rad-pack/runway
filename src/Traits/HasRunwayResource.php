<?php

namespace DoubleThreeDigital\Runway\Traits;

use DoubleThreeDigital\Runway\AugmentedRecord;
use DoubleThreeDigital\Runway\Data\HasAugmentedInstance;
use Statamic\Contracts\Data\Augmented;
use Statamic\GraphQL\ResolvesValues;

trait HasRunwayResource
{
    use HasAugmentedInstance;

    use ResolvesValues {
        resolveGqlValue as traitResolveGqlValue;
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedRecord($this);
    }
}
