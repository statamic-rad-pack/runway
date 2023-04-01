<?php

namespace DuncanMcClean\Runway\UpdateScripts;

use DuncanMcClean\Runway\Resource;
use DuncanMcClean\Runway\Runway;
use Illuminate\Support\Facades\File;
use Statamic\UpdateScripts\UpdateScript;
use Statamic\Facades\Yaml;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class MigrateSectionsToTabs extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0');
    }

    public function update()
    {
        Runway::allResources()
            ->each(function (Resource $resource) {
                $blueprintValue = config('runway.resources.'.get_class($resource->model()).'.blueprint');

                // If blueprint value is an array, then update the blueprint in the config file.
                if (is_array($blueprintValue)) {
                    $configContents = File::get(config_path('runway.php'));

                    $configContents = str_replace("sections", "tabs", $configContents);

                    File::put(config_path('runway.php'), $configContents);
                }

                // If blueprint value is a string, then we need to get the blueprint file and update it.
                if (is_string($blueprintValue)) {
                    $blueprint = $resource->blueprint();

                    $blueprintContents = Yaml::parse(File::get($blueprint->path()));

                    $blueprintContents['tabs'] = $blueprintContents['sections'];

                    unset($blueprintContents['sections']);

                    File::put($blueprint->path(), Yaml::dump($blueprintContents));
                }
            });
    }
}
