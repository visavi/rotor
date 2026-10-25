<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\User\UserRules;
use App\Models\User;
use App\Services\UserService;
use App\Support\Registry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UserRequest extends FormRequest
{
    private ?User $targetUser = null;

    public function rules(): array
    {
        return [
            'level'    => ['required', Rule::in(User::ALL_GROUPS)],
            'password' => ['nullable', 'string', 'between:6,20'],
            // Администратор чёрные списки не проверяет
            'email'  => UserRules::email(UserService::isEmailRequired(), $this->targetUser()?->id, blacklist: false),
            'name'   => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'between:3,25'],
            'themes' => ['nullable', Rule::in(getAvailableThemes())],
            // Счётчики — столбцы int: отрицательное и сверх диапазона отсекаются до записи
            'point'     => ['nullable', 'integer', 'between:0,2147483647'],
            'money'     => ['nullable', 'integer', 'between:0,2147483647'],
            'posrating' => ['nullable', 'integer', 'between:0,2147483647'],
            'negrating' => ['nullable', 'integer', 'between:0,2147483647'],
            ...UserRules::profile(),
        ];
    }

    public function messages(): array
    {
        return [
            ...UserRules::messages(),
            ...UserRules::passwordMessages('password'),
            'level'  => __('users.user_level_invalid'),
            'status' => __('users.status_short_or_long'),
            'themes' => __('users.theme_not_installed'),
        ];
    }

    /**
     * Поля модулей проверяют хуки; для админа обязательность не проверяется
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $user = $this->targetUser()) {
                    return;
                }

                foreach (Registry::$onProfileValidate as $handler) {
                    $handler($user, $this, $validator, false);
                }
            },
        ];
    }

    /**
     * Редактируемый пользователь из ?user=
     */
    public function targetUser(): ?User
    {
        return $this->targetUser ??= getUserByLogin($this->input('user'));
    }

    /**
     * Телефон хранится цифрами с необязательным +
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => UserRules::normalizePhone($this->input('phone'))]);
    }
}
