<?php

namespace DoubleThreeDigital\Runway\Data;

use Statamic\Contracts\Data\Augmented;

/**
 * This trait is a copy of HasAugmentedInstance built into Statamic BUT
 * without the __get, __set, __call methods which mess with Eloquent.
 */
trait HasAugmentedInstance
{
    public function augmentedValue($key)
    {
        return $this->augmented()->get($key);
    }

    public function toAugmentedCollection($keys = null)
    {
        return $this->augmented()
            ->withRelations($this->defaultAugmentedRelations())
            ->select($keys ?? $this->defaultAugmentedArrayKeys());
    }

    public function toAugmentedArray($keys = null)
    {
        return $this->toAugmentedCollection($keys)->all();
    }

    public function toShallowAugmentedCollection()
    {
        return $this->augmented()->select($this->shallowAugmentedArrayKeys())->withShallowNesting();
    }

    public function toShallowAugmentedArray()
    {
        return $this->toShallowAugmentedCollection()->all();
    }

    public function augmented()
    {
        return $this->newAugmentedInstance();
    }

    abstract public function newAugmentedInstance(): Augmented;

    protected function defaultAugmentedArrayKeys()
    {
        return null;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'title', 'api_url'];
    }

    protected function defaultAugmentedRelations()
    {
        return [];
    }

    public function toEvaluatedAugmentedArray($keys = null)
    {
        $collection = $this->toAugmentedCollection($keys);

        // Can't just chain ->except() because it would return a new
        // collection and the existing 'withRelations' would be lost.
        if ($exceptions = $this->excludedEvaluatedAugmentedArrayKeys()) {
            $collection = $collection
                ->except($exceptions)
                ->withRelations($collection->getRelations());
        }

        return $collection->withEvaluation()->toArray();
    }

    protected function excludedEvaluatedAugmentedArrayKeys()
    {
        return null;
    }
}
