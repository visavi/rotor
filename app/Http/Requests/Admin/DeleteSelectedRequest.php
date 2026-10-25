<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Удаление записей, отмеченных в списке чекбоксами del[]
 */
class DeleteSelectedRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'del'   => ['required', 'array'],
            'del.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'del'   => __('validator.deletion'),
            'del.*' => __('validator.deletion'),
        ];
    }

    /**
     * Возвращает id отмеченных записей
     */
    public function ids(): array
    {
        return array_map(intval(...), $this->safe()->array('del'));
    }
}
