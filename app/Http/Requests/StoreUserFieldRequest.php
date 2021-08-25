<?php

namespace App\Http\Requests;

use App\Models\UserField;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserFieldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '_token'   => 'in:' . csrf_token(),
            'type'     => 'in:' . implode(',', UserField::TYPES),
            'name'     => 'required|max:50',
            'min'      => 'required',
            'max'      => 'required',
            'required' => 'boolean'
        ];
    }
}
