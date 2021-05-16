<?php

namespace DoubleThreeDigital\Runway\UpdateScripts;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Statamic\UpdateScripts\UpdateScript;

class ModelsToResources extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.0.0-beta.2');
    }

    public function update()
    {
        if ($configurationIsCached = app()->configurationIsCached()) {
            Artisan::call('config:clear');
        }

        $contents = File::get(config_path('runway.php'));
        $contents = str_replace("'models'", "'resources'", $contents);

        File::put(config_path('runway.php'), $contents);

        if ($configurationIsCached) {
            Artisan::call('config:cache');
        }
    }
}
