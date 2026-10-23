<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Validation\Validator;

class StatusRequest extends AppearanceRequest
{
    protected function field(): string
    {
        return 'status';
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'between:3,25'],
        ];
    }

    public function messages(): array
    {
        return [
            'status' => __('users.status_short_or_long'),
        ];
    }

    /**
     * Забаненный статус не меняет
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (auth()->user()?->isBanned()) {
                    $validator->errors()->add('status', __('users.status_changed_not_ban'));
                }
            },
            ...parent::after(),
        ];
    }
}
