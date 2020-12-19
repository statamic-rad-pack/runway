<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Support\ModelFinder;
use DoubleThreeDigital\Runway\Tags\RunwayTag;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
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

        $this->publishes([
            __DIR__.'/../config/runway.php' => config_path('runway.php'),
        ], 'runway-config');

        Statamic::booted(function () {
            ModelFinder::bootModels();

            Nav::extend(function ($nav) {
                foreach (ModelFinder::all() as $model) {
                    $nav->content($model['name'])
                        ->route('runway.index', ['model' => $model['_handle']]);
                }
            });

            foreach (ModelFinder::all() as $model) {
                Permission::register("View {$model['_handle']}", function ($permission) use ($model) {
                    $permission->children([
                        Permission::make("Edit {$model['_handle']}")->children([
                            Permission::make("Create new {$model['_handle']}"),
                            Permission::make("Delete {$model['_handle']}"),
                        ]),
                    ]);
                })->group('Runway');
            }
        });
    }
}
