<?php

namespace DuncanMcClean\Runway\Console\Commands;

use DuncanMcClean\Runway\Resource;
use DuncanMcClean\Runway\Runway;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class ListResources extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runway:resources';

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
            return $this->error("Your application doesn't have any resources.");
        }

        $this->table(
            ['Handle', 'Model', 'Blueprint', 'Route'],
            Runway::allResources()->map(fn (Resource $resource) => [
                $resource->handle(),
                $resource->model()::class,
                optional($resource->blueprint())->namespace().optional($resource->blueprint())->handle(),
                $resource->hasRouting() ? $resource->route() : 'N/A',
            ])->toArray()
        );
    }
}
