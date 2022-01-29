<?php

namespace DoubleThreeDigital\Runway\Exceptions;

class ResourceNotFound extends \Exception
{
    protected $resourceHandle;

    public function __construct(string $resourceHandle)
    {
        $this->resourceHandle = $resourceHandle;

        parent::__construct("Runway could not find [{$resourceHandle}]. Please ensure its configured properly and you're using the correct handle.");
    }
}
