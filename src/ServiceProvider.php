<?php

namespace DoubleThreeDigital\Runway;

use Closure;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Search\Searchables;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\DeleteModel::class,
    ];

    protected $commands = [
        Console\Commands\GenerateBlueprint::class,
        Console\Commands\GenerateMigration::class,
        Console\Commands\RebuildUriCache::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\BelongsToFieldtype::class,
        Fieldtypes\HasManyFieldtype::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
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

            $this->registerPermissions();
            $this->registerSearchables();
            $this->bootGraphQl();

            $this->app->get(\Statamic\Contracts\Data\DataRepository::class)
                ->setRepository('runway', Routing\ResourceRoutingRepository::class);
        });
    }

    protected function registerPermissions()
    {
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
    }

    protected function registerSearchables()
    {
        Searchables::register('runway', function ($resource, $config) {
            $documents = collect();

            Runway::allResources()->each(function (Resource $resource) use ($config, &$documents) {
                $records = $resource->model()->all();

                $records->each(function ($record) use ($resource, $config, &$documents) {
                    $fields = $config['fields'];
                    $transformers = $config['transformers'] ?? [];

                    $fields = collect($fields)
                        ->mapWithKeys(function ($field) use ($record) {
                            return [$field => $record->{$field}];
                        })->flatMap(function ($value, $field) use ($transformers) {
                            if (! isset($transformers[$field]) || ! $transformers[$field] instanceof Closure) {
                                return [$field => $value];
                            }

                            $transformedValue = $transformers[$field]($value);

                            if (is_array($transformedValue)) {
                                return $transformedValue;
                            }

                            return [$field => $transformedValue];
                        })
                        ->all();

                    $documents->push(
                        (new \Statamic\Search\Document)
                            ->reference("runway::{$resource->handle()}::{$record->{$resource->primaryKey()}}")
                            ->data($fields)
                    );
                });
            });

            return $documents;
        });
    }

    protected function bootGraphQl()
    {
        Runway::allResources()
            ->each(function (Resource $resource) {
                $this->app->bind("runway.graphql.types.{$resource->handle()}", function () use ($resource) {
                    return new \DoubleThreeDigital\Runway\GraphQL\ResourceType($resource);
                });

                GraphQL::addType("runway.graphql.types.{$resource->handle()}");
            })
            ->filter
            ->graphqlEnabled()
            ->each(function (Resource $resource) {
                $this->app->bind("runway.graphql.queries.{$resource->handle()}.index", function () use ($resource) {
                    return new \DoubleThreeDigital\Runway\GraphQL\ResourceIndexQuery($resource);
                });

                $this->app->bind("runway.graphql.queries.{$resource->handle()}.show", function () use ($resource) {
                    return new \DoubleThreeDigital\Runway\GraphQL\ResourceShowQuery($resource);
                });

                GraphQL::addQuery("runway.graphql.queries.{$resource->handle()}.index");
                GraphQL::addQuery("runway.graphql.queries.{$resource->handle()}.show");
            });
    }
}
