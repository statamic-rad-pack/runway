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

        $model = $resource->model()::find($this->record);

        return User::current()->can('edit', [$resource, $model]);
    }
}
