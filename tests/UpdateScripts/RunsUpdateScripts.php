<?php

namespace StatamicRadPack\Runway\Tests\UpdateScripts;

trait RunsUpdateScripts
{
    /**
     * Run update script in your tests without checking package version.
     *
     * @param  string  $fqcn
     * @param  string  $package
     */
    protected function runUpdateScript($fqcn, $package = 'doublethreedigital/runway')
    {
        $script = new $fqcn($package);

        $script->update();
    }
}
