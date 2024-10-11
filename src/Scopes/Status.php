<?php

namespace StatamicRadPack\Runway\Scopes;

use Illuminate\Support\Collection;
use Statamic\Query\Scopes\Filters\Status as BaseStatusFilter;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class Status extends BaseStatusFilter
{
    public function visibleTo($key): bool
    {
        return in_array($key, ['runway']) && $this->resource()->hasPublishStates();
    }

    protected function options(): Collection
    {
        return collect([
            'published' => __('Published'),
            //            'scheduled' => __('Scheduled'),
            //            'expired' => __('Expired'),
            'draft' => __('Draft'),
        ]);
    }

    protected function resource(): Resource
    {
        return Runway::findResource($this->context['resource']);
    }
}
