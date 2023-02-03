<?php

namespace DoubleThreeDigital\Runway;

use DoubleThreeDigital\Runway\Search\Provider as SearchProvider;
use DoubleThreeDigital\Runway\Search\Searchable;
use Illuminate\Support\Facades\Event;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Permission;
use Statamic\Facades\Search;
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
            SearchProvider::register();
            $this->bootModelEventListeners();

            if (Runway::usesRouting()) {
                $this->app->get(\Statamic\Contracts\Data\DataRepository::class)
                    ->setRepository('runway-resources', Routing\ResourceRoutingRepository::class);
            }
        });
    }

    protected function permissionLabel($permission, $resource)
    {
        $translationKey = "runway.permissions.{$permission}";

        $label = trans($translationKey, [
            'resource' => $resource->name(),
        ]);

        if ($label == $translationKey) {
            return match ($permission) {
                'view' => "View {$resource->name()}",
                'edit' => "Edit {$resource->name()}",
                'create' => "Create {$resource->name()}",
                'delete' => "Delete {$resource->name()}"
            };
        }

        return $label;
    }

    protected function registerPermissions()
    {
        foreach (Runway::allResources() as $resource) {
            Permission::register("view {$resource->handle()}", function ($permission) use ($resource) {
                $permission
                    ->label($this->permissionLabel('view', $resource))
                    ->children([
                        Permission::make("edit {$resource->handle()}")
                            ->label($this->permissionLabel('edit', $resource))
                            ->children([
                                Permission::make("create {$resource->handle()}")
                                    ->label($this->permissionLabel('create', $resource)),
                                Permission::make("delete {$resource->handle()}")
                                    ->label($this->permissionLabel('delete', $resource)),
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

                $nav->content($resource->name())
                    ->section(__('Content'))
                    ->icon($resource->cpIcon())
                    ->route('runway.index', ['resourceHandle' => $resource->handle()])
                    ->can("view {$resource->handle()}");
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

    protected function bootModelEventListeners()
    {
        Runway::allResources()
            ->map(fn ($resource) => get_class($resource->model()))
            ->each(function ($class) {
                Event::listen('eloquent.saved: ' . $class, fn ($model) => Search::updateWithinIndexes(new Searchable($model)));
                Event::listen('eloquent.deleted: ' . $class, fn ($model) => Search::deleteFromIndexes(new Searchable($model)));
            });
    }
}
