<?php

namespace StatamicRadPack\Runway\Exceptions;

use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Spatie\Ignition\Contracts\BaseSolution;

class EmptyBlueprintException extends \Exception implements ProvidesSolution
{
    public function __construct(protected string $resourceHandle)
    {
        parent::__construct("There are no fields defined in the {$this->resourceHandle} blueprint.");
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create("Add fields to the {$this->resourceHandle} blueprint")
            ->setSolutionDescription('Before you can view this resource in the Control Panel, you need to define fields in its blueprint.')
            ->setDocumentationLinks([
                'Edit blueprint' => cp_route('blueprints.edit', ['namespace' => 'runway', 'handle' => $this->resourceHandle]),
                'Review the docs' => 'https://runway.duncanmcclean.com/blueprints',
            ]);
    }
}
