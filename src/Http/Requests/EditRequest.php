<?php

namespace DoubleThreeDigital\Runway\Http\Requests;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class EditRequest extends FormRequest
{
    public function authorize()
    {
        $resource = Runway::findResource($this->resourceHandle);

        return User::current()->can('edit', $resource);
    }

    public function rules()
    {
        return [];
    }
}
