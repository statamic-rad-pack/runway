<?php

namespace StatamicRadPack\Runway\Exceptions;

use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use StatamicRadPack\Runway\Ignition\Solutions\AddTraitToModel;

class TraitMissingException extends \Exception implements ProvidesSolution
{
    public function __construct(protected string $model)
    {
        parent::__construct("The HasRunwayResource trait is missing from the [{$model}] model");
    }

    public function getSolution(): Solution
    {
        return new AddTraitToModel($this->model);
    }
}
