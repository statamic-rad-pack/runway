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
        return $user->can('edit', [$item->runwayResource(), $item]);
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
        $models->each(fn ($model) => $model->published(false)->save());
    }
}
