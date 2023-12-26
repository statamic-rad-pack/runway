<?php

namespace StatamicRadPack\Runway\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class EditRequest extends FormRequest
{
    public function authorize()
    {
        $resource = $this->route('resource');

        $model = $resource->model()::find($this->record);

        return User::current()->can('edit', [$resource, $model]);
    }
}
