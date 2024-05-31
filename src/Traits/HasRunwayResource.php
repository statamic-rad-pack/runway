<?php

namespace StatamicRadPack\Runway\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Statamic\Contracts\Data\Augmented;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Hidden;
use Statamic\Fieldtypes\Section;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Revisions\Revisable;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use StatamicRadPack\Runway\Data\AugmentedModel;
use StatamicRadPack\Runway\Data\HasAugmentedInstance;
use StatamicRadPack\Runway\Fieldtypes\HasManyFieldtype;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

trait HasRunwayResource
{
    use FluentlyGetsAndSets, HasAugmentedInstance, Revisable;
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

    protected function revisionKey()
    {
        return vsprintf('resources/%s/%s', [
            $this->runwayResource()->handle(),
            $this->getKey(),
        ]);
    }

    protected function revisionAttributes()
    {
        return [
            'id' => $this->getKey(),
            'published' => false, // todo
            'date' => null,
            'data' => $this->getAttributes(),
        ];
    }

    public function makeFromRevision($revision)
    {
        $model = clone $this;

        if (! $revision) {
            return $model;
        }

        $attrs = $revision->attributes();

        foreach ($attrs as $key => $value) {
            $model->setAttribute($key, $value);
        }

        return $model;
    }

    public function revisionsEnabled()
    {
        return $this->runwayResource()->revisionsEnabled();
    }

    public function published($published = null)
    {
        return $this;
    }

    public function updateLastModified($user = false)
    {
        // who knows where this is coming from 🤷‍♂️
        unset($this->date);
        unset($this->data);

        return $this;
    }

    public function publish($options = [])
    {
        if (method_exists($this, 'revisionsEnabled') && $this->revisionsEnabled()) {
            return $this->publishWorkingCopy($options);
        }

        $this->save();
//        $this->published(true)->save();

        return $this;
    }

    public function unpublish($options = [])
    {
        if (method_exists($this, 'revisionsEnabled') && $this->revisionsEnabled()) {
            return $this->unpublishWorkingCopy($options);
        }

        $this->save();
//        $this->published(false)->save();

        return $this;
    }
}
