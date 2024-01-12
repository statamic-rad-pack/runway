<?php

namespace StatamicRadPack\Runway\UpdateScripts;

use Illuminate\Support\Facades\Artisan;
use Statamic\UpdateScripts\UpdateScript;

class MigrateBlueprints extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        Artisan::call('runway:migrate-blueprints');
    }
}
