<?php

namespace StatamicRadPack\Runway\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        $model = $this->route('model');

        if ($model->runwayResource()->readOnly()) {
            return false;
        }

        return User::current()->can('edit', [$model->runwayResource(), $model]);
    }
}
