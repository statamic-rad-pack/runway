<?php

namespace DoubleThreeDigital\Runway\Console\Commands;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Blueprint;

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
        \Doctrine\DBAL\Types\ArrayType::class => [
            'type' => 'array',
        ],
        \Doctrine\DBAL\Types\AsciiType::class => [],
        \Doctrine\DBAL\Types\BigIntType::class => [
            'normal' => 'integer',
        ],
        \Doctrine\DBAL\Types\BinaryType::class => [],
        \Doctrine\DBAL\Types\BlobType::class => [],
        \Doctrine\DBAL\Types\BooleanType::class => [
            'normal' => 'toggle',
        ],
        \Doctrine\DBAL\Types\DateImmutableType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\DateTimeImmutableType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\DateTimeType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\DateTimeTzImmutableType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\DateTimeTzType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\DateType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\DecimalType::class => [
            'normal' => 'floatval',
        ],
        \Doctrine\DBAL\Types\FloatType::class => [
            'normal' => 'floatval',
        ],
        \Doctrine\DBAL\Types\GuidType::class => [],
        \Doctrine\DBAL\Types\IntegerType::class => [
            'normal' => 'integer',
        ],
        \Doctrine\DBAL\Types\JsonType::class => [
            'normal' => 'array',
        ],
        \Doctrine\DBAL\Types\ObjectType::class => [],
        \Doctrine\DBAL\Types\PhpDateTimeMappingType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\PhpIntegerMappingType::class => [
            'normal' => 'integer',
        ],
        \Doctrine\DBAL\Types\SimpleArrayType::class => [
            'normal' => 'array',
        ],
        \Doctrine\DBAL\Types\SmallIntType::class => [
            'normal' => 'integer',
        ],
        \Doctrine\DBAL\Types\StringType::class => [
            'normal' => 'text',
        ],
        \Doctrine\DBAL\Types\TextType::class => [
            'normal' => 'textarea',
        ],
        \Doctrine\DBAL\Types\TimeImmutableType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\TimeType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\VarDateTimeImmutableType::class => [
            'normal' => 'date',
        ],
        \Doctrine\DBAL\Types\VarDateTimeType::class => [
            'normal' => 'date',
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
        $this->info('Generating blueprints...');
        $this->line('');

        if (! class_exists('Doctrine\DBAL\Exception')) {
            return $this->line('❌ Failed. Please install `doctrine/dbal` and try again. `composer require doctrine/dbal`');
        }

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

        $this->info('✔️ Done');
    }

    protected function generateForResource(Resource $resource)
    {
        $errorMessages = [];

        $columns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($resource->databaseTable());

        $fields = collect($columns)
            ->reject(fn (\Doctrine\DBAL\Schema\Column $column) => $column->getName() === 'id')
            ->map(fn (\Doctrine\DBAL\Schema\Column $column) => [
                'name' => $column->getName(),
                'type' => $this->getMatchingFieldtype($column),
                'nullable' => ! $column->getNotnull(),
                'default' => $column->getDefault(),
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

    protected function getMatchingFieldtype(\Doctrine\DBAL\Schema\Column $column): ?string
    {
        $match = $this->matching[$column->getType()::class] ?? null;

        if (! $match) {
            return null;
        }

        if ($match['normal'] === 'text' && $column->getName() === 'slug') {
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
