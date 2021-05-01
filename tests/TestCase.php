<?php

namespace DoubleThreeDigital\Runway\Tests;

use DoubleThreeDigital\Runway\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations, RefreshDatabase, WithFaker;

    protected $shouldFakeVersion = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/__fixtures__/database/migrations');

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->andReturn('3.1.0-testing');
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
                                    [
                                        'handle' => 'author_id',
                                        'field' => [
                                            'type' => 'belongs_to',
                                            'model' => 'author',
                                            'max_items' => 1,
                                            'mode' => 'default',
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

                Author::class => [
                    'name' => 'Author',
                    'blueprint' => [
                        'sections' => [
                            'main' => [
                                'fields' => [
                                    [
                                        'handle' => 'name',
                                        'field' => [
                                            'type' => 'text',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'listing' => [
                        'columns' => [
                            'name',
                        ],
                        'sort' => [
                            'column' => 'name',
                            'direction' => 'asc',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function postFactory(int $count = 1, array $attributes = [])
    {
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = Post::create(array_merge($attributes, [
                'title' => join(' ', $this->faker->words(6)),
                'body' => join(' ', $this->faker->paragraphs(10)),
                'author_id' => $this->authorFactory()->id,
            ]));
        }

        return count($items) === 1
            ? $items[0]
            : $items;
    }

    public function authorFactory(int $count = 1, array $attributes = [])
    {
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = Author::create(array_merge($attributes, [
                'name' => $this->faker->name(),
            ]));
        }

        return count($items) === 1
            ? $items[0]
            : $items;
    }
}

class Post extends Model
{
    protected $fillable = [
        'title', 'body', 'author_id',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}

class Author extends Model
{
    protected $fillable = [
        'name',
    ];

    public function posts()
    {
        return $this->hasMany(Author::class);
    }
}
