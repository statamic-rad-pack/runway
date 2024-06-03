<?php

namespace StatamicRadPack\Runway\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Statamic\Contracts\Data\Augmented;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Hidden;
use Statamic\Fieldtypes\Section;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use StatamicRadPack\Runway\Data\AugmentedModel;
use StatamicRadPack\Runway\Data\HasAugmentedInstance;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

trait HasRunwayResource
{
    use FluentlyGetsAndSets, HasAugmentedInstance;
    use ResolvesValues {
        resolveGqlValue as traitResolveGqlValue;
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedModel($this);
    }

    public function shallowAugmentedArrayKeys()
    {
        return [$this->runwayResource()->primaryKey(), $this->runwayResource()->titleField(), 'api_url'];
    }

    public function runwayResource(): Resource
    {
        return Runway::findResourceByModel($this);
    }

    public function reference(): string
    {
        return "runway::{$this->runwayResource()->handle()}::{$this->getKey()}";
    }

    public function scopeRunwaySearch(Builder $query, string $searchQuery)
    {
        $this->runwayResource()->blueprint()->fields()->all()
            ->reject(function (Field $field) {
                return $field->fieldtype() instanceof HasManyFieldtype
                    || $field->fieldtype() instanceof Hidden
                    || $field->fieldtype() instanceof Section
                    || $field->visibility() === 'computed';
            })
            ->each(fn (Field $field) => $query->orWhere($field->handle(), 'LIKE', '%'.$searchQuery.'%'));
    }

    public function publishedStatus(): ?string
    {
        if (! $this->runwayResource()->hasPublishStates()) {
            return null;
        }

        if (! $this->{$this->runwayResource()->publishedColumn()}) {
            return 'draft';
        }

        return 'published';
    }

    public function scopeRunwayStatus(Builder $query, string $status): void
    {
        if (! $this->runwayResource()->hasPublishStates()) {
            return;
        }

        switch ($status) {
            case 'published':
                $query->where($this->runwayResource()->publishedColumn(), true);
                break;
            case 'draft':
                $query->where($this->runwayResource()->publishedColumn(), false);
                break;
            case 'scheduled':
                throw new \Exception("Runway doesn't currently support the [scheduled] status.");
            case 'expired':
                throw new \Exception("Runway doesn't currently support the [expired] status.");
            default:
                throw new \Exception("Invalid status [$status]");
        }
    }

    public function scopeWhereStatus(Builder $query, string $status): void
    {
        $this->scopeRunwayStatus($query, $status);
    }

    public function resolveGqlValue($field)
    {
        if ($this->runwayResource()->handle() && $field === 'status') {
            return $this->publishedStatus();
        }

        return $this->traitResolveGqlValue($field);
    }
}
