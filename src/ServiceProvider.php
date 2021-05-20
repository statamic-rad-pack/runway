<?php

namespace DoubleThreeDigital\Runway;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        Console\Commands\RebuildUriCache::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\BelongsToFieldtype::class,
    ];

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
    ];

    protected $tags = [
        Tags\RunwayTag::class,
    ];

    protected $updateScripts = [
        UpdateScripts\ModelsToResources::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'runway');
        $this->mergeConfigFrom(__DIR__.'/../config/runway.php', 'runway');

        if (! config('runway.disable_migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->publishes([
            __DIR__.'/../config/runway.php' => config_path('runway.php'),
        ], 'runway-config');

        Statamic::booted(function () {
            Runway::discoverResources();

            Nav::extend(function ($nav) {
                foreach (Runway::allResources() as $resource) {
                    if ($resource->hidden()) {
                        continue;
                    }

                    $nav->content($resource->name())
                        ->icon($resource->cpIcon())
                        ->route('runway.index', ['resourceHandle' => $resource->handle()]);
                }
            });

            foreach (Runway::allResources() as $resource) {
                Permission::register("View {$resource->plural()}", function ($permission) use ($resource) {
                    $permission->children([
                        Permission::make("Edit {$resource->plural()}")->children([
                            Permission::make("Create new {$resource->singular()}"),
                            Permission::make("Delete {$resource->singular()}"),
                        ]),
                    ]);
                })->group('Runway');
            }

            $this->app->get(\Statamic\Contracts\Data\DataRepository::class)
                ->setRepository('runway-resources', Routing\ResourceRoutingRepository::class);
        });
    }
}
