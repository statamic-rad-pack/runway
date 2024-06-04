<?php

namespace StatamicRadPack\Runway\Actions;

use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;
use StatamicRadPack\Runway\Exceptions\ResourceNotFound;
use StatamicRadPack\Runway\Resource;
use StatamicRadPack\Runway\Runway;

class DuplicateModel extends Action
{
    private $newItems;

    public static function title()
    {
        return __('Duplicate');
    }

    public function visibleTo($item)
    {
        try {
            $resource = Runway::findResourceByModel($item);
        } catch (ResourceNotFound $e) {
            return false;
        }

        return $item instanceof Model && $resource->readOnly() !== true;
    }

    public function visibleToBulk($items)
    {
        return $items
            ->map(fn ($item) => $this->visibleTo($item))
            ->filter(fn ($isVisible) => $isVisible === true)
            ->count() === $items->count();
    }

    public function authorize($user, $item)
    {
        $resource = Runway::findResourceByModel($item);

        return $user->can('create', $resource);
    }

    public function buttonText()
    {
        /* @translation */
        return 'Duplicate|Duplicate :count items?';
    }

    public function dirtyWarningText()
    {
        /** @translation */
        return 'Any unsaved changes will not be duplicated into the new model.';
    }

    public function run($items, $values)
    {
        $resource = Runway::findResourceByModel($items->first());

        $this->newItems = $items->map(fn ($original) => $this->duplicateModel($original, $resource));
    }

    private function duplicateModel(Model $original, Resource $resource): Model
    {
        $model = $original->replicate();

        if ($resource->titleField()) {
            $model->setAttribute($resource->titleField(), $original->getAttribute($resource->titleField()).' (Duplicate)');
        }

        if ($resource->hasPublishStates()) {
            $model->published(false);
        }

        $model->save();

        return $model;
    }

    public function redirect($items, $values)
    {
        if ($this->context['view'] !== 'form') {
            return;
        }

        $item = $this->newItems->first();

        return $item->runwayEditUrl();
    }
}
