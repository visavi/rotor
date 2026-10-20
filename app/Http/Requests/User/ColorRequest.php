<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

class ColorRequest extends AppearanceRequest
{
    protected function field(): string
    {
        return 'color';
    }

    public function rules(): array
    {
        return [
            'color' => ['nullable', 'regex:/^#[a-f0-9]{6}$/i'],
        ];
    }

    public function messages(): array
    {
        return [
            'color' => __('validator.color'),
        ];
    }
}
