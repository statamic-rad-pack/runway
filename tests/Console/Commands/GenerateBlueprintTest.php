<?php

namespace StatamicRadPack\Runway\Tests\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as FieldsBlueprint;
use StatamicRadPack\Runway\Tests\TestCase;

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
        Schema::shouldReceive('dropIfExists')->times(3);

        Schema::shouldReceive('getColumns')
            ->with('posts')
            ->andReturn([
                [
                    'name' => 'id',
                    'type_name' => 'integer',
                    'type' => 'integer',
                    'nullable' => false,
                    'default' => null,
                ],
                [
                    'name' => 'title',
                    'type_name' => 'string',
                    'type' => 'string',
                    'nullable' => true,
                    'default' => null,
                ],
                [
                    'name' => 'description',
                    'type_name' => 'text',
                    'type' => 'text',
                    'nullable' => false,
                    'default' => null,
                ],
                [
                    'name' => 'price',
                    'type_name' => 'integer',
                    'type' => 'integer',
                    'nullable' => false,
                    'default' => 4242,
                ],
                [
                    'name' => 'metadata',
                    'type_name' => 'json',
                    'type' => 'json',
                    'nullable' => false,
                    'default' => null,
                ],
                [
                    'name' => 'date',
                    'type_name' => 'date',
                    'type' => 'date',
                    'nullable' => true,
                    'default' => null,
                ],
                [
                    'name' => 'created_at',
                    'type_name' => 'datetime',
                    'type' => 'datetime',
                    'nullable' => true,
                    'default' => null,
                ],
                [
                    'name' => 'updated_at',
                    'type_name' => 'datetime',
                    'type' => 'datetime',
                    'nullable' => true,
                    'default' => null,
                ],
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
            ->expectsOutputToContain('Generating blueprints...')
            ->expectsOutputToContain('✔️ Done')
            ->assertExitCode(0);
    }

    /** @test */
    public function can_generate_resource_with_column_that_can_not_be_matched_to_a_fieldtype()
    {
        Schema::shouldReceive('dropIfExists')->times(3);

        Schema::shouldReceive('getColumns')
            ->with('posts')
            ->andReturn([
                [
                    'name' => 'title',
                    'type_name' => 'string',
                    'type' => 'string',
                    'nullable' => true,
                    'default' => null,
                ],
                [
                    'name' => 'blob',
                    'type_name' => 'blob',
                    'type' => 'blob',
                    'nullable' => true,
                    'default' => null,
                ],
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
            ->expectsOutputToContain('Generating blueprints...')
            ->expectsOutput('❌ Posts')
            ->expectsOutputToContain('Column [blob] could not be matched with a fieldtype.')
            ->expectsOutputToContain('✔️ Done')
            ->assertExitCode(0);
    }
}
