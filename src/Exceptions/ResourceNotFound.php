<?php

namespace StatamicRadPack\Runway\Exceptions;

class ResourceNotFound extends \Exception
{
    public function __construct(protected string $resourceHandle)
    {
        parent::__construct("Runway could not find [{$resourceHandle}]. Please ensure its configured properly and you're using the correct handle.");
    }
}
