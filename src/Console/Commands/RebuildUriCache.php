<?php

namespace StatamicRadPack\Runway\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\View\Antlers\Parser;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Routing\RunwayUri;
use StatamicRadPack\Runway\Runway;

class RebuildUriCache extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runway:rebuild-uris';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Rebuild Runway's URI cache of resources.";

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
        $confirm = $this->confirm(
            'You are about to rebuild your entire URI cache. This may take part of your site down while running. Are you sure you want to continue?'
        );

        if (! $confirm) {
            return;
        }

        RunwayUri::all()->each->delete();

        Runway::allResources()
            ->each(function (Resource $resource) {
                $this->newLine(2);
                $this->info("Building {$resource->name()} URIs");

                if (! $resource->hasRouting()) {
                    $this->warn("Skipping {$resource->name()}, routing not configured.");

                    return;
                }

                $this->withProgressBar($resource->model()->all(), function ($model) use ($resource) {
                    $uri = (new Parser())
                        ->parse($resource->route(), $model->toAugmentedArray())
                        ->__toString();

                    $uri = Str::start($uri, '/');

                    $model->runwayUri()->create([
                        'uri' => $uri,
                    ]);
                });
            });
    }
}
