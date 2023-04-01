<?php

namespace DuncanMcClean\Runway\Http\Requests;

use DuncanMcClean\Runway\Runway;
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

        return User::current()->can('edit', $resource);
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
