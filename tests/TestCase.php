<?php

namespace DoubleThreeDigital\Runway\Tests;

use DoubleThreeDigital\Runway\ServiceProvider;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Rebing\GraphQL\GraphQLServiceProvider;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected $shouldFakeVersion = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->runLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/__fixtures__/database/migrations');

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->andReturn('4.0.0-testing');
            $this->addToAssertionCount(-1); // Dont want to assert this
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            GraphQLServiceProvider::class,
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'doublethreedigital/runway' => [
                'id' => 'doublethreedigital/runway',
                'namespace' => 'DoubleThreeDigital\\Runway',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));

        $app['config']->set('view.paths', [
            __DIR__.'/__fixtures__/resources/views',
        ]);

        $configs = [
            'assets',
            'cp',
            'forms',
            'static_caching',
            'sites',
            'stache',
            'system',
            'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set(
                "statamic.$config",
                require(__DIR__."/../vendor/statamic/cms/config/{$config}.php")
            );
        }

        $app['config']->set('statamic.api.enabled', true);
        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('statamic.users.repository', 'file');

        $app['config']->set('statamic.stache.stores.users', [
            'class' => UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        $app['config']->set('runway', require(__DIR__.'/__fixtures__/config/runway.php'));

        Statamic::booted(function () {
            Blueprint::setDirectory(__DIR__.'/__fixtures__/resources/blueprints');
        });
    }
}
