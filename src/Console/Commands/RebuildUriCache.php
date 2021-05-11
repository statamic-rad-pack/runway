<?php

namespace DoubleThreeDigital\Runway\Console\Commands;

use DoubleThreeDigital\Runway\AugmentedRecord;
use DoubleThreeDigital\Runway\Models\RunwayUri;
use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Console\Command;
use Statamic\View\Antlers\Parser;

class RebuildUriCache extends Command
{
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
            "You are about to rebuild your entire URI cache. This may take part of your site down while running. Are you sure you want to continue?"
        );

        if (! $confirm) {
            return;
        }

        RunwayUri::all()->each->delete();

        Runway::allResources()
            ->each(function (Resource $resource) {
                $this->info("Building {$resource->name()} URIs");

                if (! $resource->route()) {
                    $this->warn("Skipping {$resource->name()}, routing not configured.");

                    return;
                }

                $resource->model()->all()->each(function ($model) use ($resource) {
                    $this->line("{$resource->name()}: {$model->{$resource->primaryKey()}}");

                    $augmentedModel = AugmentedRecord::augment($model, $resource->blueprint());

                    $uri = (new Parser)
                        ->parse($resource->route(), $augmentedModel)
                        ->__toString();

                    $model->runwayUri()->create([
                        'uri' => $uri,
                    ]);
                });
            });
    }
}
