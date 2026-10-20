<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use App\Support\Registry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Анкета пользователя: одни правила на сайт и API
 */
class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'between:3,20'],
            ...UserRules::profile(),
        ];
    }

    public function messages(): array
    {
        return [
            ...UserRules::messages(),
            'name' => __('users.name_short_or_long'),
        ];
    }

    /**
     * Поля модулей; обязательность проверяется — анкету заполняет сам пользователь
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach (Registry::$onProfileValidate as $handler) {
                    $handler(getUser(), $this, $validator, true);
                }
            },
        ];
    }

    /**
     * Поля анкеты к сохранению
     *
     * @return array<string, mixed>
     */
    public function profile(): array
    {
        return [
            'name'     => $this->validated('name'),
            'gender'   => $this->input('gender') === User::MALE ? User::MALE : User::FEMALE,
            'country'  => $this->string('country')->substr(0, 30)->value(),
            'city'     => $this->string('city')->substr(0, 50)->value(),
            'phone'    => $this->validated('phone'),
            'site'     => $this->validated('site'),
            'birthday' => $this->validated('birthday'),
            'info'     => $this->validated('info'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => UserRules::normalizePhone($this->input('phone'))]);
    }
}
