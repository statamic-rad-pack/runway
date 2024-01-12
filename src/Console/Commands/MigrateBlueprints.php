<?php

namespace StatamicRadPack\Runway\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as FieldsBlueprint;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class MigrateBlueprints extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runway:migrate-blueprints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Part of the v6.0 upgrade. Migrates your blueprints so they can be managed in the Control Panel.';

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
        Runway::allResources()
            ->each(function (Resource $resource) {
                $originalBlueprint = $this->resolveOriginalBlueprint($resource);

                $resource->blueprint()->setContents($originalBlueprint->contents())->save();

                if (File::exists($originalBlueprint->path())) {
                    File::delete($originalBlueprint->path());
                }
            });
    }

    protected function resolveOriginalBlueprint(Resource $resource): FieldsBlueprint
    {
        if (is_string($resource->config()->get('blueprint'))) {
            return Blueprint::find($resource->config()->get('blueprint'));
        }

        if (is_array($resource->config()->get('blueprint'))) {
            return Blueprint::make()->setHandle($resource->handle())->setContents($resource->config()->get('blueprint'));
        }

        throw new \Exception("Could not resolve the original blueprint for the [{$resource->handle()}] resource.");
    }
}
