<?php

namespace DoubleThreeDigital\Runway\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        $resource = $this->route('resource');

        if ($resource->readOnly()) {
            return false;
        }

        return User::current()->can('create', $resource);
    }
}
