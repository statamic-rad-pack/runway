<?php

namespace StatamicRadPack\Runway\Actions;

use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;

class Unpublish extends Action
{
    public static function title()
    {
        return __('Unpublish');
    }

    public function visibleTo($item)
    {
        return $this->context['view'] === 'list'
            && $item instanceof Model
            && $item->runwayResource()->readOnly() !== true
            && $item->published() === true;
    }

    public function visibleToBulk($items)
    {
        return $items->every(fn ($item) => $this->visibleTo($item));
    }

    public function authorize($user, $item)
    {
        return $user->can("publish {$item->runwayResource()->handle()}");
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to unpublish this model?|Are you sure you want to unpublish these :count models?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Unpublish Model|Unpublish :count Models';
    }

    public function run($models, $values)
    {
        $failures = $models->reject(fn ($model) => $model->published(false)->save());
        $total = $models->count();

        if ($failures->isNotEmpty()) {
            $success = $total - $failures->count();
            if ($total === 1) {
                throw new \Exception(__('Model could not be unpublished'));
            } elseif ($success === 0) {
                throw new \Exception(__('Models could not be unpublished'));
            } else {
                throw new \Exception(__(':success/:total models were unpublished', ['total' => $total, 'success' => $success]));
            }
        }

        return trans_choice('Model unpublished|Models unpublished', $total);
    }
}
