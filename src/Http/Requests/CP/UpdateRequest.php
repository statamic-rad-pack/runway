<?php

namespace StatamicRadPack\Runway\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        $resource = $this->route('resource');

        if ($resource->readOnly()) {
            return false;
        }

        $model = $resource->model()->find($this->model);

        return User::current()->can('edit', [$resource, $model]);
    }
}
