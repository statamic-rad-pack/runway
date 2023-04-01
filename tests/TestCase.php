<?php

namespace DuncanMcClean\Runway\Tests;

use DuncanMcClean\Runway\Routing\Traits\RunwayRoutes;
use DuncanMcClean\Runway\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Rebing\GraphQL\GraphQLServiceProvider;
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

        $this->runLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/__fixtures__/database/migrations');

        $this->withoutVite();

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->andReturn('3.1.0-testing');
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
            'duncanmcclean/runway' => [
                'id' => 'duncanmcclean/runway',
                'namespace' => 'DuncanMcClean\\Runway',
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

        $app['config']->set('view.paths', [
            __DIR__.'/__fixtures__/resources/views',
        ]);

        $app['config']->set('runway', [
            'resources' => [
                Post::class => [
                    'name' => 'Posts',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    [
                                        'handle' => 'title',
                                        'field' => [
                                            'type' => 'text',
                                        ],
                                    ],
                                    [
                                        'handle' => 'slug',
                                        'field' => [
                                            'type' => 'slug',
                                        ],
                                    ],
                                    [
                                        'handle' => 'body',
                                        'field' => [
                                            'type' => 'textarea',
                                        ],
                                    ],
                                    [
                                        'handle' => 'author_id',
                                        'field' => [
                                            'type' => 'belongs_to',
                                            'resource' => 'author',
                                            'max_items' => 1,
                                            'mode' => 'default',
                                        ],
                                    ],
                                    [
                                        'handle' => 'age',
                                        'field' => [
                                            'type' => 'integer',
                                            'visibility' => 'computed',
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
                    'route' => '/posts/{{ slug }}',
                ],

                Author::class => [
                    'name' => 'Author',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    [
                                        'handle' => 'name',
                                        'field' => [
                                            'type' => 'text',
                                        ],
                                    ],
                                    // [
                                    //     'handle' => 'posts',
                                    //     'field' => [
                                    //         'type' => 'has_many',
                                    //         'resource' => 'post',
                                    //         'mode' => 'select',
                                    //     ],
                                    // ],
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
            $items[] = Post::create(array_merge([
                'title' => $title = implode(' ', $this->faker->words(6)),
                'slug' => str_slug($title),
                'body' => implode(' ', $this->faker->paragraphs(10)),
                'author_id' => $this->authorFactory()->id,
            ], $attributes));
        }

        return count($items) === 1
            ? $items[0]
            : $items;
    }

    public function authorFactory(int $count = 1, array $attributes = [])
    {
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = Author::create(array_merge([
                'name' => $this->faker->name(),
            ], $attributes));
        }

        return count($items) === 1
            ? $items[0]
            : $items;
    }
}

class Post extends Model
{
    use RunwayRoutes;

    protected $fillable = [
        'title', 'slug', 'body', 'author_id',
    ];

    public function scopeFood($query)
    {
        $query->whereIn('title', ['Pasta', 'Apple', 'Burger']);
    }

    public function scopeFruit($query, $smth)
    {
        if ($smth === 'idoo') {
            $query->whereIn('title', ['Apple']);
        }
    }

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
        return $this->hasMany(Post::class);
    }
}
