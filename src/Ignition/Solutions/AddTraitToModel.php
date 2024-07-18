<?php

namespace StatamicRadPack\Runway\Ignition\Solutions;

use Archetype\Facades\PHPFile;
use Illuminate\Support\Str;
use Spatie\Ignition\Contracts\RunnableSolution;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class AddTraitToModel implements RunnableSolution
{
    public function __construct(protected $model = null) {}

    public function getSolutionTitle(): string
    {
        $model = Str::after($this->model, 'App\\Models\\');

        return "Add HasRunwayResource trait to the {$model} model";
    }

    public function getSolutionDescription(): string
    {
        return 'You need to add the `HasRunwayResource` trait to your model in order to use it with Runway.';
    }

    public function getDocumentationLinks(): array
    {
        return [
            'Learn more' => 'https://runway.duncanmcclean.com/resources#content-defining-resources',
        ];
    }

    public function getSolutionActionDescription(): string
    {
        return 'Runway can attempt to add it for you.';
    }

    public function getRunButtonText(): string
    {
        return 'Add trait';
    }

    public function run(array $parameters = []): void
    {
        PHPFile::load($parameters['model'])
            ->add()->use([HasRunwayResource::class])
            ->add()->useTrait('HasRunwayResource')
            ->save();
    }

    public function getRunParameters(): array
    {
        return [
            'model' => $this->model,
        ];
    }
}
