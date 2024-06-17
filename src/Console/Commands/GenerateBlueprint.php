<?php

namespace StatamicRadPack\Runway\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class GenerateBlueprint extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runway:generate-blueprints {resource?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate blueprints from your models';

    /**
     * The matching table for column types -> fieldtypes.
     */
    protected array $matching = [
        'array' => ['type' => 'array'],
        'bigint' => ['normal' => 'integer'],
        'boolean' => ['normal' => 'toggle'],
        'date_immutable' => ['normal' => 'date'],
        'datetime_immutable' => ['normal' => 'date'],
        'datetime' => ['normal' => 'date'],
        'datetimetz_immutable' => ['normal' => 'date'],
        'datetimetz' => ['normal' => 'date'],
        'decimal' => ['normal' => 'float'],
        'float' => ['normal' => 'float'],
        'int' => ['normal' => 'integer'],
        'integer' => ['normal' => 'integer'],
        'json' => ['normal' => 'array'],
        'simple_array' => ['normal' => 'array'],
        'smallint' => ['normal' => 'integer'],
        'string' => ['normal' => 'text'],
        'text' => ['normal' => 'textarea'],
        'time_immutable' => ['normal' => 'date'],
        'time' => ['normal' => 'date'],
        'timestamp' => ['normal' => 'date'],
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->components->info('Generating blueprints...');
        $this->line('');

        $resources = [];

        if ($resourceHandle = $this->argument('resource')) {
            $resources[] = Runway::findResource($resourceHandle);
        }

        if (count($resources) === 0) {
            Runway::allResources()->each(function ($resource) use (&$resources) {
                $resources[] = $resource;
            });
        }

        foreach ($resources as $resource) {
            $this->generateForResource($resource);
        }

        $this->components->info('✔️ Done');
    }

    protected function generateForResource(Resource $resource)
    {
        $errorMessages = [];

        $columns = Schema::getColumns($resource->databaseTable());

        $fields = collect($columns)
            ->reject(fn (array $column) => $column['name'] === 'id')
            ->map(fn (array $column) => [
                'name' => $column['name'],
                'type' => $this->getMatchingFieldtype($column),
                'nullable' => $column['nullable'],
                'default' => $column['default'],
                'original_column' => $column,
            ])
            ->each(function ($field) use (&$errorMessages) {
                if (is_null($field['type'])) {
                    $errorMessages[] = "Column [{$field['name']}] could not be matched with a fieldtype.";
                }
            })
            ->all();

        $this->generateNewBlueprint($resource, $fields);

        if (count($errorMessages) === 0) {
            $this->components->info("✔️ {$resource->name()}");
            $this->line('');
        } else {
            $this->line("❌ {$resource->name()}");

            foreach ($errorMessages as $errorMessage) {
                $this->components->error($errorMessage);
            }

            $this->line('');
        }
    }

    protected function getMatchingFieldtype(array $column): ?string
    {
        $match = $this->matching[$column['type_name']] ?? null;

        if (! $match) {
            return null;
        }

        if ($match['normal'] === 'text' && $column['name'] === 'slug') {
            return 'slug';
        }

        return $match['normal'];
    }

    protected function generateNewBlueprint(Resource $resource, array $fields)
    {
        $mainSection = [];
        $sidebarSection = [];

        $sidebarFields = ['slug', 'uuid', 'created_at', 'updated_at'];

        collect($fields)
            ->each(function ($field) use (&$mainSection, &$sidebarSection, $sidebarFields) {
                $fieldContents = [
                    'handle' => $field['name'],
                    'field' => [
                        'type' => $field['type'],
                        'display' => Str::studly($field['name']),
                    ],
                ];

                if (! $field['nullable']) {
                    $fieldContents['field']['validate'] = 'required';
                }

                if (in_array($field['name'], $sidebarFields)) {
                    $sidebarSection[] = $fieldContents;
                } else {
                    $mainSection[] = $fieldContents;
                }
            });

        $resource->blueprint()->setContents([
            'tabs' => [
                'main' => ['fields' => $mainSection],
                'sidebar' => ['fields' => $sidebarSection],
            ],
        ])->save();
    }
}
