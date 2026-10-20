<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReglistRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'choice'   => ['required', 'array'],
            'choice.*' => ['integer'],
            'action'   => ['required', 'in:yes,no'],
        ];
    }

    public function messages(): array
    {
        return [
            'choice'   => __('admin.reglists.users_not_selected'),
            'choice.*' => __('admin.reglists.users_not_selected'),
            'action'   => __('main.action_not_selected'),
        ];
    }

    /**
     * Возвращает id отмеченных пользователей
     */
    public function ids(): array
    {
        return intar($this->validated('choice'));
    }
}
