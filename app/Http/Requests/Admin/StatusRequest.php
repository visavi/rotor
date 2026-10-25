<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'between:3,30'],
            'color'   => ['nullable', 'regex:/^#[a-f0-9]{6}$/i'],
            'topoint' => ['nullable', 'integer', 'between:0,2147483647'],
            'point'   => ['nullable', 'integer', 'between:0,2147483647'],
        ];
    }

    public function messages(): array
    {
        return [
            'name'  => __('statuses.status_length'),
            'color' => __('validator.color'),
        ];
    }
}
