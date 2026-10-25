<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\Attributes\RedirectTo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

/**
 * Смена пароля: одни правила на сайт и API
 */
#[RedirectTo('settings#tab-account')]
class PasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'old_password'     => ['required', 'string'],
            'new_password'     => UserRules::password(),
            'confirm_password' => ['required', 'same:new_password'],
        ];
    }

    public function messages(): array
    {
        return [
            ...UserRules::passwordMessages('new_password'),
            'old_password'     => __('users.password_not_different'),
            'confirm_password' => __('users.passwords_different'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = auth()->user() ?? abort(403);
                $old = $this->input('old_password');
                $new = $this->input('new_password');

                // Хук идёт и после проваленных правил: массив из ?new_password[]= уже отклонён
                if (! is_string($new)) {
                    $new = '';
                }

                if (is_string($old) && ! Hash::check($old, $user->password)) {
                    $validator->errors()->add('old_password', __('users.password_not_different'));
                }

                if (Hash::check($new, $user->password)) {
                    $validator->errors()->add('new_password', __('users.password_different'));
                }

                if (strcasecmp($new, $user->login) === 0) {
                    $validator->errors()->add('new_password', __('users.login_different'));
                }
            },
        ];
    }
}
