<?php

namespace StatamicRadPack\Runway\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Entries\Collection;
use Statamic\Facades;
use Stillat\Proteus\Support\Facades\ConfigWriter;
use Whoops\Run;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class ImportCollection extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runway:import-collection
        { collection? : The handle of the collection to import. }
        { --force : Force overwrite if files already exist }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a collection into the database.';

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
        $collection = $this->promptForCollection();

        $modelName = Str::of($collection->handle())->singular()->title();

        if (class_exists($model = "App\\Models\\{$modelName}") && ! $this->option('force')) {
            $this->components->error("A [{$model}] model already exists.");

            return 1;
        }

        // todo: come back and fill in the fillable and casts arrays later
        // todo: when routing is enabled, add trait to the model
        $modelContents = Str::of(File::get(__DIR__.'/stubs/model.stub'))
            ->replace('{{ namespace }}', 'App\Models')
            ->replace('{{ class }}', $modelName)
            ->replace('{{ fillable }}', '// TODO')
            ->replace('{{ casts }}', '// TODO')
            ->__toString();

        File::put(app_path("Models/{$modelName}.php"), $modelContents);

        ConfigWriter::write(
            'runway.resources.\\'.str_replace('\\', '\\', $model),
            array_filter([
                'handle' => $collection->handle(),
                'name' => $collection->title(),
                'published' => true,
                'revisions' => $collection->revisionsEnabled(),
                'route' => $collection->route(Facades\Site::default()->handle()),
                'template' => $collection->template(),
                'layout' => $collection->layout(),
                'order_by' => $collection->sortField(),
                'order_by_direction' => $collection->sortDirection(),
            ])
        );

        Facades\Blueprint::make("runway::{$collection->handle()}")
            ->setContents($collection->entryBlueprint()->contents())
            ->save();

        $columns = [
            ['type' => 'uuid'],
            ['type' => 'string', 'name' => 'title'],
            ['type' => 'string', 'name' => 'slug'],
            ['type' => 'boolean', 'name' => 'published'],
            // todo: blueprint fields
            ['type' => 'timestamps'],
        ];

        // todo: generate blueprint
        // todo: run `php artisan migrate`

        // todo: import entries
        // todo: prompt user to delete collection and entries

        $this->components->info("The {$collection->title()} collection has been imported.");

        $this->line('  Next steps:');

        $this->components->bulletList([
            'Replace usage of the {{ collection }} tag in your templates with the {{ runway }} tag.',
            'Replace Entry fields with the Belongs To / Has Many fieldtypes.',
        ]);
    }

    private function promptForCollection(): Collection
    {
        $handle = $this->argument('collection') ?? select(
            label: 'Which collection would you like to import?',
            options: Facades\Collection::all()
                ->mapWithKeys(fn (Collection $collection) => [$collection->handle() => $collection->title()])
                ->all(),
        );

        $collection = Facades\Collection::find($handle);

        if ($collection->entryBlueprints()->count() > 1) {
            if (! confirm("Runway doesn't support multiple blueprints. Only the first blueprint will be imported. Are you sure you want to continue?")) {
                exit;
            }
        }

        if ($collection->sites()->count() > 1) {
            if (! confirm("Runway doesn't support localization. Are you sure you want to continue?")) {
                exit;
            }
        }

        if ($collection->hasStructure()) {
            if (! confirm("Runway doesn't support trees. Are you sure you want to continue?")) {
                exit;
            }
        }

        return $collection;
    }
}
