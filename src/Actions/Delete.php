<?php

namespace DoubleThreeDigital\Runway\Actions;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;

class Delete extends Action
{
    protected $dangerous = true;

    public static function title()
    {
        return __('Delete');
    }

    public function visibleTo($item)
    {
        return $item instanceof Model;
    }

    public function visibleToBulk($items)
    {
        if ($items->whereInstanceOf(Model::class)->count() !== $items->count()) {
            return false;
        }

        return true;
    }

    public function authorize($user, $item)
    {
        $resource = Runway::findResourceByModel($item);

        return $user->hasPermission("Delete {$resource->plural()}")
            || $user->isSuper();
    }

    public function buttonText()
    {
        /** @translation */
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function run($items, $values)
    {
        $items->each->delete();
    }
}
