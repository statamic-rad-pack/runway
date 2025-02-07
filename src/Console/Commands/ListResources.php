<?php

namespace StatamicRadPack\Runway\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class ListResources extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:runway:resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Runway resources.';

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
        if (! Runway::allResources()->count()) {
            return $this->components->error("Your application doesn't have any resources.");
        }

        $this->table(
            ['Handle', 'Model', 'Route'],
            Runway::allResources()->map(fn (Resource $resource) => [
                $resource->handle(),
                $resource->model()::class,
                $resource->hasRouting() ? $resource->route() : 'N/A',
            ])->values()->toArray()
        );
    }
}
