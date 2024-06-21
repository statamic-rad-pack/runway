<?php

namespace StatamicRadPack\Runway\Exceptions;

class TraitMissingException extends \Exception
{
    public function __construct(public string $model)
    {
        parent::__construct("The HasRunwayResource trait is missing from the [{$model}] model");
    }
}
