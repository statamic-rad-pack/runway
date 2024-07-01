<?php

namespace StatamicRadPack\Runway\Actions;

use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;

class Publish extends Action
{
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
        return $user->can('edit', [$item->runwayResource(), $item]);
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
        $models->each(fn ($model) => $model->published(true)->save());
    }
}
