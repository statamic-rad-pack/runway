<?php

namespace DoubleThreeDigital\Runway\Tests\Console\Commands;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\TestCase;
use DoubleThreeDigital\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Spatie\TestTime\TestTime;
use SplFileInfo;

class GenerateMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('runway', [
            'resources' => [
                Food::class => [''],
                Drink::class => [''],
            ],
        ]);

        collect(File::glob(database_path('migrations/*')))->each(function ($path) {
            File::delete($path);
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();

        collect(File::allFiles(database_path('migrations')))->each(function (SplFileInfo $file) {
            File::delete($file->getRealPath());
        });
    }

    /** @test */
    public function can_generate_migrations_for_multiple_resources()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'name', 'field' => ['type' => 'text', 'validate' => 'required']],
                                    ['handle' => 'metadata->calories', 'field' => ['type' => 'integer', 'validate' => 'required']],
                                ],
                            ],
                        ],
                    ],
                ],
                Drink::class => [
                    'name' => 'Drink',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'name', 'field' => ['type' => 'text', 'validate' => 'required']],
                                    ['handle' => 'metadata->calories', 'field' => ['type' => 'integer', 'validate' => 'required']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        $this
            ->artisan('runway:generate-migrations')
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->execute();

        $this->assertFileExists(
            $expectedFoodsMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php'
        );

        $this->assertStringContainsString('return new class extends Migration', File::get($expectedFoodsMigrationPath));
        $this->assertStringContainsString("Schema::create('foods', function (Blueprint", File::get($expectedFoodsMigrationPath));
        $this->assertStringContainsString('$table->text(\'name\')', File::get($expectedFoodsMigrationPath));
        $this->assertStringContainsString('$table->json(\'metadata\')', File::get($expectedFoodsMigrationPath));

        $this->assertFileExists(
            $expectedDrinksMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_drinks_table.php'
        );

        $this->assertStringContainsString('return new class extends Migration', File::get($expectedDrinksMigrationPath));
        $this->assertStringContainsString("Schema::create('drinks', function (Blueprint", File::get($expectedDrinksMigrationPath));
        $this->assertStringContainsString('$table->text(\'name\')', File::get($expectedDrinksMigrationPath));
        $this->assertStringContainsString('$table->json(\'metadata\')', File::get($expectedDrinksMigrationPath));

        $this->assertFalse(Schema::hasTable('foods'));
        $this->assertFalse(Schema::hasTable('drinks'));
    }

    /** @test */
    public function can_generate_migration_for_single_resource()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'name', 'field' => ['type' => 'text', 'validate' => 'required']],
                                    ['handle' => 'metadata->calories', 'field' => ['type' => 'integer', 'validate' => 'required']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->execute();

        $this->assertFileExists(
            $expectedMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php'
        );

        $this->assertStringContainsString('return new class extends Migration', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->text(\'name\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->json(\'metadata\')', File::get($expectedMigrationPath));

        $this->assertFalse(Schema::hasTable('foods'));
    }

    /** @test */
    public function cant_generate_migration_where_table_already_exists()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'name', 'field' => ['type' => 'text', 'validate' => 'required']],
                                    ['handle' => 'metadata->calories', 'field' => ['type' => 'integer', 'validate' => 'required']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        Schema::shouldReceive('hasTable')
            ->with('foods')
            ->andReturn(true);

        Schema::shouldReceive('dropIfExists');

        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->expectsOutput('Table [foods] already exists. Runway is not smart enough yet to update existing migrations. Sorry!')
            ->execute();

        $this->assertFileDoesNotExist(database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php');
    }

    /** @test */
    public function can_generate_migration_and_run_them_afterwards()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'name', 'field' => ['type' => 'text', 'validate' => 'required']],
                                    ['handle' => 'metadata->calories', 'field' => ['type' => 'integer', 'validate' => 'required']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        $this->assertFalse(Schema::hasTable('foods'));

        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'yes')
            ->execute();

        $this->assertFileExists(database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php');

        $this->assertTrue(Schema::hasTable('foods'));
    }

    /** @test */
    public function can_generate_migration_and_ensure_normal_field_is_correct()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    [
                                        'handle' => 'ramond_the_array',
                                        'field' => [
                                            'type' => 'array',
                                        ],
                                    ],
                                    [
                                        'handle' => 'the_big_red_button',
                                        'field' => [
                                            'type' => 'button_group',
                                        ],
                                    ],
                                    [
                                        'handle' => 'floating_away',
                                        'field' => [
                                            'type' => 'float',
                                        ],
                                    ],
                                    [
                                        'handle' => 'int_the_ant',
                                        'field' => [
                                            'type' => 'integer',
                                        ],
                                    ],
                                    [
                                        'handle' => 'toggle_me_smth',
                                        'field' => [
                                            'type' => 'toggle',
                                        ],
                                    ],
                                    [
                                        'handle' => 'author_id',
                                        'field' => [
                                            'type' => 'belongs_to',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->execute();

        $this->assertFileExists(
            $expectedMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php'
        );

        $this->assertStringContainsString('return new class extends Migration', File::get($expectedMigrationPath));

        $this->assertStringContainsString('$table->json(\'ramond_the_array\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->string(\'the_big_red_button\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->float(\'floating_away\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->integer(\'int_the_ant\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->boolean(\'toggle_me_smth\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->bigInteger(\'author_id\')', File::get($expectedMigrationPath));

        $this->assertFalse(Schema::hasTable('foods'));
    }

    /** @test */
    public function can_generate_migration_and_ensure_max_items_1_field_is_correct()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'assets', 'field' => ['type' => 'assets']],
                                    ['handle' => 'collections', 'field' => ['type' => 'collections']],
                                    ['handle' => 'entries', 'field' => ['type' => 'entries']],
                                    ['handle' => 'terms', 'field' => ['type' => 'terms']],
                                    ['handle' => 'users', 'field' => ['type' => 'users']],

                                    ['handle' => 'asset', 'field' => ['type' => 'assets', 'max_items' => 1]],
                                    ['handle' => 'collection', 'field' => ['type' => 'collections', 'max_items' => 1]],
                                    ['handle' => 'entry', 'field' => ['type' => 'entries', 'max_items' => 1]],
                                    ['handle' => 'term', 'field' => ['type' => 'terms', 'max_items' => 1]],
                                    ['handle' => 'user', 'field' => ['type' => 'users', 'max_items' => 1]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->execute();

        $this->assertFileExists(
            $expectedMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php'
        );

        $this->assertStringContainsString('return new class extends Migration', File::get($expectedMigrationPath));

        $this->assertStringContainsString('$table->json(\'assets\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->json(\'collections\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->json(\'entries\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->json(\'terms\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->json(\'users\')', File::get($expectedMigrationPath));

        $this->assertStringContainsString('$table->string(\'asset\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->string(\'collection\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->string(\'entry\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->string(\'term\')', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->string(\'user\')', File::get($expectedMigrationPath));

        $this->assertFalse(Schema::hasTable('foods'));
    }

    /** @test */
    public function can_generate_migration_and_ensure_field_is_nullable_if_required_not_set()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'text', 'field' => ['type' => 'text']],
                                    ['handle' => 'entries', 'field' => ['type' => 'entries']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->execute();

        $this->assertFileExists(
            $expectedMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php'
        );

        $this->assertStringContainsString('return new class extends Migration', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->text(\'text\')->nullable();', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->json(\'entries\')->nullable();', File::get($expectedMigrationPath));

        $this->assertFalse(Schema::hasTable('foods'));
    }

    /** @test */
    public function can_generate_migration_and_ensure_field_is_not_nullable_if_required_set()
    {
        TestTime::freeze();

        Config::set('runway', [
            'resources' => [
                Food::class => [
                    'name' => 'Food',
                    'blueprint' => [
                        'tabs' => [
                            'main' => [
                                'fields' => [
                                    ['handle' => 'text', 'field' => ['type' => 'text', 'validate' => 'required']],
                                    ['handle' => 'entries', 'field' => ['type' => 'entries', 'validate' => 'required']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Runway::discoverResources();

        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->execute();

        $this->assertFileExists(
            $expectedMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_table.php'
        );

        $this->assertStringContainsString('return new class extends Migration', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->text(\'text\');', File::get($expectedMigrationPath));
        $this->assertStringContainsString('$table->json(\'entries\');', File::get($expectedMigrationPath));

        $this->assertFalse(Schema::hasTable('foods'));
    }
}

class Food extends Model
{
    use HasRunwayResource;

    protected $table = 'foods';

    protected $fillable = ['name'];
}

class Drink extends Model
{
    use HasRunwayResource;

    protected $table = 'drinks';

    protected $fillable = ['name'];
}
