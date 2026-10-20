<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Notice;
use App\Rules\TextLengthRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoticeRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'between:5,100'],
            'text' => ['required', 'string', new TextLengthRule(10, 65000)],
        ];

        // Тип задаётся только при создании
        if (! $this->route('id')) {
            $rules['type'] = [
                'required',
                'string',
                'regex:/^[a-z0-9_\-]+$/i',
                'between:3,20',
                Rule::unique(Notice::class, 'type'),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.regex'  => __('admin.notices.notice_invalid'),
            'type.unique' => __('admin.notices.notice_exists'),
            'type'        => __('admin.notices.notice_length'),
            'name'        => __('validator.text'),
            'text'        => __('validator.text'),
        ];
    }
}
