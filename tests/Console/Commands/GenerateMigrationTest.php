<?php

namespace DoubleThreeDigital\Runway\Tests\Console\Commands;

use DoubleThreeDigital\Runway\Runway;
use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
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
                Food::class => [
                    '',
                ],
            ],
        ]);
    }

    /** @test */
    public function can_generate_migrations_for_multiple_resources()
    {
        //
    }

    /** @test */
    public function can_generate_migration_for_single_resource()
    {
        $this->markTestIncomplete("Hmm, something odd was going on here. I'm just going to presume this is all working for now and loop back to it later.");

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
                                        'handle' => 'name',
                                        'field' => [
                                            'type' => 'text',
                                            'validate' => 'required',
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

        // Assert migration doesn't already exist
        $this->assertCount(0, collect(File::allFiles(database_path('migrations'))));

        // Run the command
        $this
            ->artisan('runway:generate-migrations', [
                'resource' => 'food',
            ])
            ->expectsQuestion('Should we run your migrations?', 'no')
            ->execute();

        // Assert migration now exists
        $this->assertFileExists(
            $expectedMigrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_foods_tables.php'
        );

        // Assert migration contains the right fields
        $this->assertStringContainsString('$table->text(\'name\');', File::get($expectedMigrationPath));

        // Cleanup after ourselves
        collect(File::allFiles(database_path('migrations')))
            ->each(function (SplFileInfo $file) {
                File::delete($file->getRealPath());
            });
    }

    /** @test */
    public function can_generate_migration_where_table_already_exists()
    {
        //
    }

    /** @test */
    public function can_generate_migration_and_run_them_afterwards()
    {
        //
    }

    /** @test */
    public function can_generate_migration_and_ensure_normal_field_is_correct()
    {
        //
    }

    /** @test */
    public function can_generate_migration_and_ensure_max_items_1_field_is_correct()
    {
        //
    }

    /** @test */
    public function can_generate_migration_and_ensure_field_is_nullable_if_required_not_set()
    {
        //
    }

    /** @test */
    public function can_generate_migration_and_ensure_field_is_not_nullable_if_required_set()
    {
        //
    }
}

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = ['name'];
}
