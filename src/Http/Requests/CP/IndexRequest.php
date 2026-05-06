<?php

namespace StatamicRadPack\Runway\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        return User::current()->can('view', $this->route('resource'));
    }
}
