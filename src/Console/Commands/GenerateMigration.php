<?php

namespace DoubleThreeDigital\Runway\Console\Commands;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Fields\Field;
use Symfony\Component\Process\Process;

class GenerateMigration extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runway:generate-migrations {resource?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate migrations from your blueprints';

    /**
     * The matching table for fieldtypes -> database columns.
     *
     * @var array
     */
    protected array $matching = [
        'array' => [
            'normal' => 'json',
        ],
        'assets' => [
            'normal' => 'json',
            'max_items_1' => 'string',
        ],
        'bard' => [
            'normal' => 'json',
            'save_html' => 'string',
        ],
        'button_group' => [
            'normal' => 'string',
        ],
        'checkboxes' => [
            'normal' => 'json',
        ],
        'code' => [
            'normal' => 'string',
        ],
        'collections' => [
            'normal' => 'json',
            'max_items_1' => 'string',
        ],
        'color' => [
            'normal' => 'string',
        ],
        'date' => [
            'normal' => 'datetime', // Need to double check this is right??
        ],
        'entries' => [
            'normal' => 'json',
            'max_items_1' => 'string',
        ],
        'fieldset' => [], // TODO: not quite sure...
        'float' => [
            'normal' => 'float',
        ],
        'grid' => [
            'normal' => 'json',
        ],
        'hidden' => [
            'normal' => 'string',
        ],
        'integer' => [
            'normal' => 'integer',
        ],
        'link' => [
            'normal' => 'json',
        ],
        'list' => [
            'normal' => 'float',
        ],
        'markdown' => [
            'normal' => 'string',
        ],
        'radio' => [
            'normal' => 'string',
        ],
        'range' => [
            'normal' => 'string',
        ],
        'replicator' => [
            'normal' => 'json',
        ],
        'select' => [
            'normal' => 'string', // TODO: technically this could also be an integer/json - depends on the options
        ],
        'slug' => [
            'normal' => 'string',
        ],
        'structures' => [
            'normal' => 'json',
        ],
        'table' => [
            'normal' => 'json',
        ],
        'tags' => [
            'normal' => 'json',
        ],
        'template' => [
            'normal' => 'string',
        ],
        'terms' => [
            'normal' => 'json',
            'max_items_1' => 'string',
        ],
        'text' => [
            'normal' => 'text',
        ],
        'textarea' => [
            'normal' => 'text',
        ],
        'time' => [
            'normal' => 'time', // TODO: in the table this was a string, needs double checked
        ],
        'toggle' => [
            'normal' => 'boolean',
        ],
        'users' => [
            'normal' => 'json',
            'max_items_1' => 'string',
        ],
        'video' => [
            'normal' => 'string',
        ],
        'yaml' => [
            'normal' => 'string',
        ],

        // Addon additions
        'belongs_to' => [
            'normal' => 'bigInteger',
        ],
        'country' => [
            'normal' => 'json',
            'max_items_1' => 'string',
        ],
        'money' => [
            'normal' => 'integer',
        ],
        'product_variant' => [
            'normal' => 'string',
        ],
        'product_variants' => [
            'normal' => 'json',
        ],
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
        $this->info('Generating migrations...');
        $this->line('');

        $resources = [];

        if ($resourceHandle = $this->argument('resource')) {
            $resources[] = Runway::findResource($resourceHandle);
        }

        if (count($resources) === 0) {
            Runway::allResources()
                ->each(function ($resource) use (&$resources) {
                    $resources[] = $resource;
                });
        }

        foreach ($resources as $resource) {
            $this->generateForResource($resource);
        }

        if ($this->ask('Should we run your migrations?')) {
            Artisan::call('migrate');
        }

        $this->info('✔️ Done');
    }

    protected function generateForResource(Resource $resource)
    {
        $errorMessages = [];

        $fields = $resource->blueprint()->fields()->all()
            ->reject(function (Field $field) {
                return in_array($field->type(), [
                    'html', 'revealer', 'section',
                ]);
            })
            ->all();

        $columns = collect($fields)
            ->map(function (Field $field) {
                return [
                    'name'           => $field->handle(),
                    'type'           => $this->getMatchingColumnType($field),
                    'nullable'       => $this->isFieldNullable($field),
                    'default'        => empty($field->defaultValue()) ? $field->defaultValue() : null,
                    'original_field' => $field,
                ];
            })
            ->each(function ($column) use (&$errorMessages) {
                if (is_null($column['type'])) {
                    $errorMessages[] = "Field [{$column['name']}] could not be matched with a column type.";
                }
            })
            ->all();

        if (Schema::hasTable($resource->databaseTable())) {
            $errorMessages[] = "Table [{$resource->databaseTable()}] already exists. Runway is not smart enough yet to update existing migrations. Sorry!";
        } else {
            $this->generateNewTableMigration($resource, $columns);
        }

        if (count($errorMessages) === 0) {
            $this->line("✔️ {$resource->name()}");
            $this->line('');
        } else {
            $this->line("❌ {$resource->name()}");

            foreach ($errorMessages as $errorMessage) {
                $this->comment($errorMessage);
            }

            $this->line('');
        }
    }

    protected function getMatchingColumnType(Field $field): ?string
    {
        $match = isset($this->matching[$field->type()])
            ? $this->matching[$field->type()]
            : null;

        if (! $match) {
            return null;
        }

        if (isset($match['max_items_1']) && isset($field->config()['max_items']) && $field->config()['max_items'] === 1) {
            return $match['max_items_1'];
        }

        return $match['normal'];
    }

    protected function isFieldNullable(Field $field): bool
    {
        $rules = $field->rules()[$field->handle()];

        if (in_array('required', $rules)) {
            return false;
        }

        return true;
    }

    protected function generateNewTableMigration(Resource $resource, array $columns)
    {
        $migrationContents = File::get(__DIR__.'/stubs/create_table_migration.php.stub');

        $columnCode = collect($columns)
            ->map(function ($column) {
                $code = '$table->'.$column['type'].'(\''.$column['name'].'\')';

                if ($column['nullable']) {
                    $code .= '->nullable()';
                }

                if ($column['default'] !== null) {
                    if (is_string($column['default'])) {
                        $code .= '->default(\''.$column['default'].'\')';
                    }

                    if (is_int($column['default']) || is_float($column['default'])) {
                        $code .= '->default('.$column['default'].')';
                    }
                }

                return $code.';';
            })
            ->join(PHP_EOL);

        $migrationContents = Str::of($migrationContents)
            ->replace('{{ClassName}}', 'Create'.Str::title($resource->databaseTable()).'Table')
            ->replace('{{TableName}}', $resource->databaseTable())
            ->replace('{{TableColumns}}', $columnCode)
            ->__toString();

        File::put(
            $migrationPath = database_path().'/migrations/'.date('Y_m_d_His').'_create_'.$resource->databaseTable().'_table.php',
            $migrationContents
        );

        $process = new Process(['./vendor/bin/php-cs-fixer', 'fix', $migrationPath, '--rules=@PSR2,@PhpCsFixer'], base_path());
        $process->run();
    }
}
