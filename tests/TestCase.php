<?php

namespace DoubleThreeDigital\Runway\Tests;

use DoubleThreeDigital\Runway\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations, RefreshDatabase;

    protected $shouldFakeVersion = true;

    protected function setUp(): void
    {
        // require_once __DIR__.'/__fixtures__/app/User.php';

        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/__fixtures__/database/migrations');

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->andReturn('3.0.0-testing');
            $this->addToAssertionCount(-1); // Dont want to assert this
        }
    }

    protected function getPackageProviders($app)
    {
        return [
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
                'id'        => 'doublethreedigital/runway',
                'namespace' => 'DoubleThreeDigital\\Runway\\',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

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

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.stores.users', [
            'class' => UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        $app['config']->set('runway', [
            'models' => [
                Post::class => [
                    'name' => 'Posts',
                    'blueprint' => [
                        'sections' => [
                            'main' => [
                                'fields' => [
                                    [
                                        'handle' => 'title',
                                        'field' => [
                                            'type' => 'text'
                                        ],
                                    ],
                                    [
                                        'handle' => 'body',
                                        'field' => [
                                            'type' => 'textarea'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'listing' => [
                        'columns' => [
                            'title',
                        ],
                        'sort' => [
                            'column' => 'title',
                            'direction' => 'desc',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function tearDown() : void
    {
        if ($this->app) {
            $this->callBeforeApplicationDestroyedCallbacks();

            $this->app = null;
        }

        parent::tearDown();
    }
}

class Post extends Model
{
    protected $fillable = [
        'title', 'body',
    ];
}
