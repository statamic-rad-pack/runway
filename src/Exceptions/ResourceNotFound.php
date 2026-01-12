<?php

namespace StatamicRadPack\Runway\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Exceptions\NotFoundHttpException;

class ResourceNotFound extends \Exception
{
    public function __construct(protected string $resourceHandle)
    {
        parent::__construct("Runway could not find [{$resourceHandle}]. Please ensure its configured properly and you're using the correct handle.");
    }

    public function render(Request $request): Response
    {
        throw new NotFoundHttpException($this->message);
    }
}
