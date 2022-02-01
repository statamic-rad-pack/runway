<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Runway;
use Facades\Statamic\View\Cascade;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Events\ResponseCreated;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Statamic\View\View;

class ResourceResponse implements Responsable
{
    protected $data;
    protected $request;
    protected $with = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toResponse($request)
    {
        $this->request = $request;

        $this
            ->addViewPaths();

        $response = response()
            ->make($this->contents());

        ResponseCreated::dispatch($response, $this->data);

        return $response;
    }

    protected function addViewPaths()
    {
        $finder = view()->getFinder();
        $amp = Statamic::isAmpRequest();

        $site = method_exists($this->data, 'site')
            ? $this->data->site()->handle()
            : Site::current()->handle();

        $paths = collect($finder->getPaths())->flatMap(function ($path) use ($site, $amp) {
            return [
                $amp ? $path.'/'.$site.'/amp' : null,
                $path.'/'.$site,
                $amp ? $path.'/amp' : null,
                $path,
            ];
        })->filter()->values()->all();

        $finder->setPaths($paths);

        return $this;
    }

    protected function contents()
    {
        $contents = (new View())
            ->template($this->data->template())
            ->layout($this->data->layout())
            ->with(
                Runway::findResourceByModel($this->data)
                    ->augment($this->data)
            )
            // ->cascadeContent($this->data)
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
