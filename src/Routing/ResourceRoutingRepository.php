<?php

namespace DoubleThreeDigital\Runway\Routing;

use DoubleThreeDigital\Runway\Resource;
use DoubleThreeDigital\Runway\Runway;
use Illuminate\Support\Str;

class ResourceRoutingRepository
{
    public function findByUri(string $uri)
    {
        $uri = Str::of($uri)->trim('/')->__toString();
        $uriSegments = $this->splitUriIntoSegments($uri);

        return Runway::allResources()
            ->map(function (Resource $resource) use ($uri, $uriSegments) {
                // This does some dirty stuff with URIs and figuring out which bits contain the variables.
                $route = $resource->route();
                $routeSegments = $this->splitUriIntoSegments($route);

                $score = 0;
                $dyanmicSegments = [];

                // Loop through URI segments
                foreach ($uriSegments as $i => $segment) {
                    $routeSegment = $routeSegments[$i];

                    // If URI segment is the same as resource route segment
                    if ($segment === $routeSegment) {
                        $score++;
                    }

                    // Loop through blueprint fields
                    foreach ($resource->blueprint()->fields()->all()->keys()->toArray() as $blueprintFieldKey) {
                        // If resource route segment contains a variable property
                        if (Str::contains($routeSegment, "{$blueprintFieldKey}")) {
                            $score++;
                            $dyanmicSegments[$blueprintFieldKey] = $i;
                        }
                    }

                    continue;
                }

                return [
                    'resource' => $resource,
                    'score' => $score,
                    'dynamicSegments' => $dyanmicSegments,
                ];
            })
            ->sortBy('score')
            ->map(function ($data) use ($uri, $uriSegments) {
                $query = $data['resource']->model()->query();

                foreach ($data['dynamicSegments'] as $variable => $segmentId) {
                    $query->where($variable, $uriSegments[$segmentId]);
                }

                if (count($data['dynamicSegments']) == 0) {
                    return null;
                }

                return $query->first();
            })
            ->first();
    }

    protected function splitUriIntoSegments(string $uri)
    {
        return array_values(array_filter(explode("/", parse_url($uri, PHP_URL_PATH)), function ($value) {
            return $value !== '';
        }));
    }
}
