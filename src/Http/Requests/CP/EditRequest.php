<?php

namespace StatamicRadPack\Runway\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class EditRequest extends FormRequest
{
    public function authorize()
    {
        $model = $this->route('resource');

        return User::current()->can('edit', [$model->runwayResource(), $model]);
    }
}
