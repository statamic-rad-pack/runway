<?php

namespace StatamicRadPack\Runway\Actions;

use Illuminate\Database\Eloquent\Model;
use Statamic\Actions\Action;
use Statamic\Facades\User;

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
            && $item->runwayResource()->hasPublishStates()
            && $item->{$item->runwayResource()->publishedColumn()};
    }

    public function visibleToBulk($items)
    {
        if ($items->reject(fn ($item) => $item->{$item->runwayResource()->publishedColumn()})->count() > 0) {
            return false;
        }

        return true;
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
        $models->each(function ($model) {
            $model->{$model->runwayResource()->publishedColumn()} = false;
            $model->save();
        });
    }
}
