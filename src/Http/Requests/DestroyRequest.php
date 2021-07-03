<?php

namespace DoubleThreeDigital\Runway\Http\Requests;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class DestroyRequest extends FormRequest
{
    public function authorize()
    {
        $resource = Runway::findResource($this->resourceHandle);

        return User::current()->hasPermission("Delete {$resource->plural()}")
            || User::current()->isSuper();
    }

    public function rules()
    {
        return [];
    }
}
