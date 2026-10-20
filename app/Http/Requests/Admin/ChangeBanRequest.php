<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\TextLengthRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangeBanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'timeban' => ['required', 'date', 'after:now'],
            'reason'  => ['required', 'string', new TextLengthRule(5, 1000)],
        ];
    }

    public function messages(): array
    {
        return [
            'timeban' => __('admin.bans.time_empty'),
            'reason'  => __('validator.text'),
        ];
    }
}
