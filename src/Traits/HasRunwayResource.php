<?php

namespace DoubleThreeDigital\Runway\Traits;

use DoubleThreeDigital\Runway\Data\AugmentedModel;
use DoubleThreeDigital\Runway\Data\HasAugmentedInstance;
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
}
