<?php

namespace StatamicRadPack\Runway\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Revisions\Revision;
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

    public function scopeWhereStatus(Builder $query, string $status): void
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

    public function resolveGqlValue($field)
    {
        if ($this->runwayResource()->handle() && $field === 'status') {
            return $this->publishedStatus();
        }

        return $this->traitResolveGqlValue($field);
    }

    public function runwayEditUrl(): string
    {
        return cp_route('runway.update', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
        ]);
    }

    public function runwayUpdateUrl(): string
    {
        return cp_route('runway.update', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
        ]);
    }

    public function runwayPublishUrl(): string
    {
        return cp_route('runway.published.store', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
        ]);
    }

    public function runwayUnpublishUrl(): string
    {
        return cp_route('runway.published.destroy', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
        ]);
    }

    public function runwayRevisionsUrl(): string
    {
        return cp_route('runway.revisions.index', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
        ]);
    }

    public function runwayRevisionUrl(Revision $revision): string
    {
        return cp_route('runway.revisions.index', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
            'revisionId' => $revision->id(),
        ]);
    }

    public function runwayRestoreRevisionUrl(): string
    {
        return cp_route('runway.restore-revision', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
        ]);
    }

    public function runwayCreateRevisionUrl(): string
    {
        return cp_route('runway.revisions.store', [
            'resource' => $this->runwayResource()->handle(),
            'model' => $this->{$this->runwayResource()->routeKey()},
        ]);
    }

    protected function revisionKey(): string
    {
        return vsprintf('resources/%s/%s', [
            $this->runwayResource()->handle(),
            $this->getKey(),
        ]);
    }

    protected function revisionAttributes(): array
    {
        return [
            'id' => $this->getKey(),
            'published' => false, // todo
            'date' => null,
            'data' => $this->getAttributes(),
        ];
    }

    public function makeFromRevision($revision): self
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

    public function revisionsEnabled(): bool
    {
        return $this->runwayResource()->revisionsEnabled();
    }

    public function published($published = null)
    {
        if (! $this->runwayResource()->hasPublishStates()) {
            return func_num_args() === 0 ? null : $this;
        }

        if (func_num_args() === 0) {
            return (bool) $this->getAttribute($this->runwayResource()->publishedColumn());
        }

        $this->setAttribute($this->runwayResource()->publishedColumn(), $published);

        return $this;
    }

    public function publish($options = [])
    {
        if ($this->revisionsEnabled()) {
            return $this->publishWorkingCopy($options);
        }

        if ($this->runwayResource()->hasPublishStates()) {
            $this->published(true)->save();
        }

        return $this;
    }

    public function unpublish($options = [])
    {
        if ($this->revisionsEnabled()) {
            return $this->unpublishWorkingCopy($options);
        }

        if ($this->runwayResource()->hasPublishStates()) {
            $this->published(false)->save();
        }

        return $this;
    }

    /**
     * We don't need to do anything here, since:
     * - The updated_at timestamp is updated automatically by the database.
     * - We don't have an updated_by column to store the user who last modified the model.
     *
     * @param $user
     * @return $this
     */
    public function updateLastModified($user = false): self
    {
        return $this;
    }
}
