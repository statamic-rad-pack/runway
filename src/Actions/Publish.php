<?php

namespace StatamicRadPack\Runway\Actions;

use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;

class Publish extends Action
{
    protected $icon = 'eye';

    public static function title()
    {
        return __('Publish');
    }

    public function visibleTo($item)
    {
        return $this->context['view'] === 'list'
            && $item instanceof Model
            && $item->runwayResource()->readOnly() !== true
            && $item->published() === false;
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
        return 'Are you sure you want to publish this model?|Are you sure you want to publish these :count models?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Publish Model|Publish :count Models';
    }

    public function run($models, $values)
    {
        $failures = $models->reject(fn ($model) => $model->published(true)->save());
        $total = $models->count();

        if ($failures->isNotEmpty()) {
            $success = $total - $failures->count();
            if ($total === 1) {
                throw new \Exception(__('Model could not be published'));
            } elseif ($success === 0) {
                throw new \Exception(__('Model could not be published'));
            } else {
                throw new \Exception(__(':success/:total models were published', ['total' => $total, 'success' => $success]));
            }
        }

        return trans_choice('Model published|Models published', $total);
    }
}
