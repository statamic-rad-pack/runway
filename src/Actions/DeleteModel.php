<?php

namespace StatamicRadPack\Runway\Actions;

use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;
use StatamicRadPack\Runway\Exceptions\ResourceNotFound;
use StatamicRadPack\Runway\Runway;

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

    public function bypassesDirtyWarning(): bool
    {
        return true;
    }

    public function run($items, $values)
    {
        $failures = $items->reject(fn ($model) => $model->delete());
        $total = $items->count();

        if ($failures->isNotEmpty()) {
            $success = $total - $failures->count();
            if ($total === 1) {
                throw new \Exception(__('Item could not be deleted'));
            } elseif ($success === 0) {
                throw new \Exception(__('Items could not be deleted'));
            } else {
                throw new \Exception(__(':success/:total items were deleted', ['total' => $total, 'success' => $success]));
            }
        }

        return trans_choice('Item deleted|Items deleted', $total);
    }

    public function redirect($items, $values)
    {
        if ($this->context['view'] !== 'form') {
            return;
        }

        $item = $items->first();

        return cp_route('runway.index', ['resource' => Runway::findResourceByModel($item)->handle()]);
    }
}
