<?php

namespace StatamicRadPack\Runway\Exceptions;

class EmptyBlueprintException extends \Exception
{
    public function __construct(protected string $resourceHandle)
    {
        parent::__construct("The blueprint for the {$this->resourceHandle} resource is empty. Please add fields to the blueprint.");
    }
}
