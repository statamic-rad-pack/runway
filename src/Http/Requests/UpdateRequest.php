<?php

namespace DoubleThreeDigital\Runway\Http\Requests;

use DoubleThreeDigital\Runway\Support\ModelFinder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $model = ModelFinder::find($this->model);

        return $model['blueprint']->fields()->validator()->rules();
    }
}
