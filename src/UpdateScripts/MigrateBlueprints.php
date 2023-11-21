<?php

namespace DoubleThreeDigital\Runway\UpdateScripts;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Yaml;
use Statamic\Fields\Blueprint as FieldsBlueprint;
use Statamic\UpdateScripts\UpdateScript;

class MigrateBlueprints extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        Runway::allResources()
            ->each(function (Resource $resource) {
                $originalBlueprint = $this->resolveOriginalBlueprint($resource);

                $resource->blueprint()->setContents($originalBlueprint->contents())->save();
            });
    }

    protected function resolveOriginalBlueprint(Resource $resource): FieldsBlueprint
    {
        if (is_string($resource->config()->get('blueprint'))) {
            return Blueprint::find($resource->config()->get('blueprint'));
        }

        if (is_array($resource->config()->get('blueprint'))) {
            return Blueprint::make()->setHandle($resource->handle())->setContents($resource->config()->get('blueprint'));
        }

        throw new \Exception("Could not resolve the original blueprint for the [{$resource->handle()}] resource.");
    }
}
