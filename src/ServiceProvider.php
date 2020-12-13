<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Support\ModelFinder;
use DoubleThreeDigital\Runway\Tags\RunwayTag;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $tags = [
        RunwayTag::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'runway');
        $this->mergeConfigFrom(__DIR__.'/../config/runway.php', 'runway');

        Statamic::booted(function () {
            ModelFinder::bootModels();

            Nav::extend(function ($nav) {
                foreach (ModelFinder::all() as $model) {
                    $nav->content($model['plural'])
                        ->route('runway.index', ['model' => $model['_handle']]);
                }
            });
        });
    }
}
