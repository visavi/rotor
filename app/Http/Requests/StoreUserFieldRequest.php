<?php

namespace App\Http\Requests;

use App\Models\UserField;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserFieldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            '_token'   => 'required|in:' . csrf_token(),
            'type'     => 'required|in:' . implode(',', UserField::TYPES),
            'name'     => 'required|max:50',
            'min'      => 'required',
            'max'      => 'required',
            'required' => 'boolean',
        ];
    }
}
