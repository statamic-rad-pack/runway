<?php

namespace StatamicRadPack\Runway\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        $resource = $this->route('resource');

        if (! $resource->canCreate() || $resource->readOnly()) {
            return false;
        }

        return User::current()->can('create', $resource);
    }
}
