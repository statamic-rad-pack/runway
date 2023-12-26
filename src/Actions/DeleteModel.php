<?php

namespace StatamicRadPack\Runway\Actions;

use StatamicRadPack\Runway\Exceptions\ResourceNotFound;
use StatamicRadPack\Runway\Runway;
use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;

class DeleteModel extends Action
{
    protected $dangerous = true;

    public static function title()
    {
        return __('Delete');
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

        return $user->can('delete', [$resource, $item]);
    }

    public function buttonText()
    {
        /* @translation */
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        /* @translation */
        return 'Are you sure you want to want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function run($items, $values)
    {
        $items->each->delete();
    }
}
