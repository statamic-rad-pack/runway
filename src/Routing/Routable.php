<?php

namespace StatamicRadPack\Runway\Routing;

use Statamic\Contracts\Routing\UrlBuilder;
use Statamic\Facades\URL;
use Statamic\Support\Str;

/**
 * This class is a direct copy of the Statamic\Routing\Routable trait,
 * just without the `slug` method & property which conflict with Eloquent
 * attributes of the same name.
 *
 * See: https://github.com/statamic-rad-pack/runway/issues/420
 */
trait Routable
{
    abstract public function route();

    abstract public function routeData();

    public function uri()
    {
        if (! $route = $this->route()) {
            return null;
        }

        return app(UrlBuilder::class)->content($this)->build($route);
    }

    public function url()
    {
        if ($this->isRedirect()) {
            return $this->redirectUrl();
        }

        return $this->urlWithoutRedirect();
    }

    public function urlWithoutRedirect()
    {
        if (! $url = $this->absoluteUrlWithoutRedirect()) {
            return null;
        }

        return URL::makeRelative($url);
    }

    public function absoluteUrl()
    {
        if ($this->isRedirect()) {
            return $this->absoluteRedirectUrl();
        }

        return $this->absoluteUrlWithoutRedirect();
    }

    public function absoluteUrlWithoutRedirect()
    {
        return $this->makeAbsolute($this->uri());
    }

    public function isRedirect()
    {
        return ($url = $this->redirectUrl())
            && $url !== 404;
    }

    public function redirectUrl()
    {
        if ($redirect = $this->value('redirect')) {
            return (new \Statamic\Routing\ResolveRedirect)($redirect, $this);
        }
    }

    public function absoluteRedirectUrl()
    {
        return $this->makeAbsolute($this->redirectUrl());
    }

    private function makeAbsolute($url)
    {
        if (! $url) {
            return null;
        }

        if (! Str::startsWith($url, '/')) {
            return $url;
        }

        $url = vsprintf('%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            ltrim($url, '/'),
        ]);

        return $url === '/' ? $url : rtrim($url, '/');
    }
}
