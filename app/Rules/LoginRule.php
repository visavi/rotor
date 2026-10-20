<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * Формат логина: латиница, цифры и до двух дефисов, 3–20 символов
 *
 * Каждое нарушение — своё сообщение, как в подсказке формы регистрации.
 * Занятость и чёрный список проверяют unique и NotBlacklistedRule
 */
class LoginRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $login = (string) $value;

        if (! preg_match('/^[a-z0-9\-]+$/i', $login)) {
            $fail(__('validator.login'));
        } elseif (! preg_match('/^[a-z0-9]$/i', Str::substr($login, 0, 1))) {
            $fail(__('users.login_begin_requirements'));
        } elseif (mb_strlen($login) < 3 || mb_strlen($login) > 20) {
            $fail(__('users.login_length_requirements'));
        } elseif (ctype_digit($login)) {
            $fail(__('users.field_characters_requirements'));
        } elseif (substr_count($login, '-') > 2) {
            $fail(__('users.login_hyphens_requirements'));
        }
    }
}
