<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\Attributes\RedirectTo;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Объяснение забаненного администрации
 */
#[RedirectTo('ban')]
class BanExplainRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'msg' => ['required', 'string', 'between:5,1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'msg' => __('validator.text'),
        ];
    }
}
