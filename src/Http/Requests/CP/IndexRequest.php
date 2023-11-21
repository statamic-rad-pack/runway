<?php

namespace DoubleThreeDigital\Runway\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        $resource = $this->route('resource');

        return User::current()->can('view', $resource);
    }
}
