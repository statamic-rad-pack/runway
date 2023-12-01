<?php

namespace DoubleThreeDigital\Runway\Tests\Console\Commands;

use DoubleThreeDigital\Runway\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as FieldsBlueprint;

class GenerateBlueprintTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        collect(File::glob(database_path('migrations/*')))->each(function ($path) {
            File::delete($path);
        });
    }

    /** @test */
    public function can_generate_blueprint()
    {
        Schema::shouldReceive('dropIfExists')->times(4);

        Schema::shouldReceive('getConnection')
            ->andReturnSelf()
            ->shouldReceive('getDoctrineSchemaManager')
            ->andReturnSelf()
            ->shouldReceive('listTableColumns')
            ->with('posts')
            ->andReturn([
                new \Doctrine\DBAL\Schema\Column(
                    'id',
                    new \Doctrine\DBAL\Types\IntegerType(),
                ),
                new \Doctrine\DBAL\Schema\Column(
                    'title',
                    new \Doctrine\DBAL\Types\StringType(),
                ),
                (new \Doctrine\DBAL\Schema\Column(
                    'description',
                    new \Doctrine\DBAL\Types\TextType(),
                ))->setNotnull(true),
                (new \Doctrine\DBAL\Schema\Column(
                    'price',
                    new \Doctrine\DBAL\Types\IntegerType(),
                ))->setDefault(4242),
                (new \Doctrine\DBAL\Schema\Column(
                    'metadata',
                    new \Doctrine\DBAL\Types\JsonType(),
                ))->setNotnull(true),
                new \Doctrine\DBAL\Schema\Column(
                    'date',
                    new \Doctrine\DBAL\Types\DateType(),
                ),
                new \Doctrine\DBAL\Schema\Column(
                    'created_at',
                    new \Doctrine\DBAL\Types\DateTimeType(),
                ),
                new \Doctrine\DBAL\Schema\Column(
                    'updated_at',
                    new \Doctrine\DBAL\Types\DateTimeType(),
                ),
            ])
            ->once();

        Blueprint::shouldReceive('find')->with('')->andReturnNull();

        Blueprint::shouldReceive('find')
            ->with('runway::post')
            ->andReturn(new FieldsBlueprint('post'))
            ->shouldReceive('setContents')
            ->with([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'title', 'field' => ['type' => 'text', 'display' => 'Title', 'validate' => 'required']],
                            ['handle' => 'description', 'field' => ['type' => 'textarea', 'display' => 'Description', 'validate' => 'required']],
                            ['handle' => 'price', 'field' => ['type' => 'integer', 'display' => 'Price', 'validate' => 'required']],
                            ['handle' => 'metadata', 'field' => ['type' => 'array', 'display' => 'Metadata', 'validate' => 'required']],
                            ['handle' => 'date', 'field' => ['type' => 'date', 'display' => 'Date', 'validate' => 'required']],
                        ],
                    ],
                    'sidebar' => [
                        'fields' => [
                            ['handle' => 'created_at', 'field' => ['type' => 'date', 'display' => 'CreatedAt', 'validate' => 'required']],
                            ['handle' => 'updated_at', 'field' => ['type' => 'date', 'display' => 'UpdatedAt', 'validate' => 'required']],
                        ],
                    ],
                ],
            ])
            ->andReturnSelf()
            ->shouldReceive('save')
            ->andReturnNull()
            ->once();

        $this->artisan('runway:generate-blueprints', ['resource' => 'post'])
            ->expectsOutput('Generating blueprints...')
            ->expectsOutput('✔️ Done')
            ->assertExitCode(0);
    }

    /** @test */
    public function can_generate_resource_with_column_that_can_not_be_matched_to_a_fieldtype()
    {
        Schema::shouldReceive('dropIfExists')->times(4);

        Schema::shouldReceive('getConnection')
            ->andReturnSelf()
            ->shouldReceive('getDoctrineSchemaManager')
            ->andReturnSelf()
            ->shouldReceive('listTableColumns')
            ->with('posts')
            ->andReturn([
                new \Doctrine\DBAL\Schema\Column(
                    'title',
                    new \Doctrine\DBAL\Types\StringType(),
                ),
                (new \Doctrine\DBAL\Schema\Column(
                    'blob',
                    new \Doctrine\DBAL\Types\BlobType(),
                )),
            ])
            ->once();

        Blueprint::shouldReceive('find')->with('')->andReturnNull();

        Blueprint::shouldReceive('find')
            ->with('runway::post')
            ->andReturn(new FieldsBlueprint('post'))
            ->shouldReceive('setContents')
            ->with([
                'tabs' => [
                    'main' => [
                        'fields' => [
                            ['handle' => 'title', 'field' => ['type' => 'text', 'display' => 'Title', 'validate' => 'required']],
                        ],
                    ],
                ],
            ])
            ->andReturnSelf()
            ->shouldReceive('save')
            ->andReturnNull()
            ->once();

        $this->artisan('runway:generate-blueprints', ['resource' => 'post'])
            ->expectsOutput('Generating blueprints...')
            ->expectsOutput('❌ Posts')
            ->expectsOutput('Column [blob] could not be matched with a fieldtype.')
            ->expectsOutput('✔️ Done')
            ->assertExitCode(0);
    }
}
