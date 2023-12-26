<?php

namespace StatamicRadPack\Runway\Traits;

use StatamicRadPack\Runway\Data\AugmentedModel;
use StatamicRadPack\Runway\Data\HasAugmentedInstance;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Statamic\Contracts\Data\Augmented;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Hidden;
use Statamic\Fieldtypes\Section;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Support\Traits\FluentlyGetsAndSets;

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
}
