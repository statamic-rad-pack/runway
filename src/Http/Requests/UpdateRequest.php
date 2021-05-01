<?php

namespace DoubleThreeDigital\Runway\Http\Requests;

use DoubleThreeDigital\Runway\Runway;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
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
