<?php

namespace StatamicRadPack\Runway\Routing;

use Facades\Statamic\View\Cascade;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Events\ResponseCreated;
use Statamic\Facades\Site;
use Statamic\View\View;

class ResourceResponse implements Responsable
{
    protected $request;

    protected $with = [];

    public function __construct(protected $data) {}

    public function toResponse($request)
    {
        $this->request = $request;

        $this->addViewPaths();

        $response = response()->make($this->contents());

        ResponseCreated::dispatch($response, $this->data);

        return $response;
    }

    protected function addViewPaths()
    {
        $finder = view()->getFinder();

        $site = method_exists($this->data, 'site')
            ? $this->data->site()->handle()
            : Site::current()->handle();

        $paths = collect($finder->getPaths())->flatMap(function ($path) use ($site) {
            return [
                $path.'/'.$site,
                $path,
            ];
        })->filter()->values()->all();

        $finder->setPaths($paths);

        return $this;
    }

    protected function contents()
    {
        $contents = (new View)
            ->template($this->data->template())
            ->layout($this->data->layout())
            ->with($this->data->toAugmentedArray())
            ->render();

        return $contents;
    }

    protected function cascade()
    {
        return Cascade::instance()->withContent($this->data)->hydrate();
    }

    public function with($data)
    {
        $this->with = $data;

        return $this;
    }
}
