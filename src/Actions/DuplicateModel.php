<?php

namespace StatamicRadPack\Runway\Actions;

use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;
use StatamicRadPack\Runway\Exceptions\ResourceNotFound;
use StatamicRadPack\Runway\Runway;

class DuplicateModel extends Action
{
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

    public function run($items, $values)
    {
        $resource = Runway::findResourceByModel($items->first());

        $items->each(function (Model $item) use ($resource) {
            $duplicateModel = $item->replicate();

            if ($resource->titleField()) {
                $duplicateModel->{$resource->titleField()} = $duplicateModel->{$resource->titleField()}.' (Duplicate)';
            }

            $duplicateModel->save();
        });
    }
}
