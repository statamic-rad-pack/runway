<?php

namespace DoubleThreeDigital\Runway\Http\Requests;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        $resource = Runway::findResource($this->resourceHandle);

        if ($resource->readOnly()) {
            return false;
        }

        return User::current()->hasPermission("Edit {$resource->plural()}")
            || User::current()->isSuper();
    }

    public function rules()
    {
        return Runway::findResource($this->resourceHandle)
            ->blueprint()
            ->fields()
            ->validator()
            ->rules();
    }
}
