<?php

namespace StatamicRadPack\Runway\Ignition\SolutionProviders;

use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use StatamicRadPack\Runway\Ignition\Solutions\AddTraitToModel;
use Throwable;

class TraitMissingException implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return $throwable instanceof \StatamicRadPack\Runway\Exceptions\TraitMissingException;
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new AddTraitToModel($throwable->model)];
    }
}
