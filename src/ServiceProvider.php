<?php

namespace DoubleThreeDigital\Runway;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\DeleteModel::class,
    ];

    protected $commands = [
        Console\Commands\GenerateBlueprint::class,
        Console\Commands\GenerateMigration::class,
        Console\Commands\ListResources::class,
        Console\Commands\RebuildUriCache::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\BelongsToFieldtype::class,
        Fieldtypes\HasManyFieldtype::class,
    ];

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__ . '/../resources/dist/js/cp.js',
    ];

    protected $tags = [
        Tags\RunwayTag::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'runway');
        $this->mergeConfigFrom(__DIR__ . '/../config/runway.php', 'runway');

        if (! config('runway.disable_migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->publishes([
            __DIR__ . '/../config/runway.php' => config_path('runway.php'),
        ], 'runway-config');

        Statamic::booted(function () {
            Runway::discoverResources();

            $this->registerPermissions();
            $this->registerNavigation();
            $this->bootGraphQl();

            if (Runway::usesRouting()) {
                $this->app->get(\Statamic\Contracts\Data\DataRepository::class)
                    ->setRepository('runway-resources', Routing\ResourceRoutingRepository::class);
            }
        });
    }

    protected function registerPermissions()
    {
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
    }

    protected function registerNavigation()
    {
        Nav::extend(function ($nav) {
            foreach (Runway::allResources() as $resource) {
                if ($resource->hidden()) {
                    continue;
                }

                $nav->content($resource->cpTitle())
                    ->section($resource->cpSection())
                    ->icon($resource->cpIcon())
                    ->route('runway.index', ['resourceHandle' => $resource->handle()])
                    ->can("View {$resource->plural()}");
            }
        });
    }

    protected function bootGraphQl()
    {
        Runway::allResources()
            ->each(function (Resource $resource) {
                $this->app->bind("runway.graphql.types.{$resource->handle()}", fn () => new \DoubleThreeDigital\Runway\GraphQL\ResourceType($resource));

                GraphQL::addType("runway.graphql.types.{$resource->handle()}");
            })
            ->filter
            ->graphqlEnabled()
            ->each(function (Resource $resource) {
                $this->app->bind("runway.graphql.queries.{$resource->handle()}.index", fn () => new \DoubleThreeDigital\Runway\GraphQL\ResourceIndexQuery($resource));

                $this->app->bind("runway.graphql.queries.{$resource->handle()}.show", fn () => new \DoubleThreeDigital\Runway\GraphQL\ResourceShowQuery($resource));

                GraphQL::addQuery("runway.graphql.queries.{$resource->handle()}.index");
                GraphQL::addQuery("runway.graphql.queries.{$resource->handle()}.show");
            });
    }
}
