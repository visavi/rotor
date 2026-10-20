<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StickerCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'between:3,50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name' => __('validator.text'),
        ];
    }
}
