<?php

namespace DuncanMcClean\Runway\Http\Requests;

use DuncanMcClean\Runway\Runway;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        $resource = Runway::findResource($this->resourceHandle);

        return User::current()->can('view', $resource);
    }

    public function rules()
    {
        return [];
    }
}
