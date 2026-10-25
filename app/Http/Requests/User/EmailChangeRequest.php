<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Services\UserService;
use Illuminate\Foundation\Http\Attributes\RedirectTo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

/**
 * Смена почты: одни правила на сайт и API
 */
#[RedirectTo('settings#tab-account')]
class EmailChangeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'    => UserRules::email(true, auth()->user()?->id),
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            ...UserRules::messages(),
            'password' => __('users.password_not_different'),
        ];
    }

    public function after(UserService $users): array
    {
        return [
            function (Validator $validator) use ($users): void {
                $user = auth()->user() ?? abort(403);

                if ($this->input('email') === $user->email) {
                    $validator->errors()->add('email', __('users.email_different'));
                }

                $password = $this->input('password');

                if (is_string($password) && ! Hash::check($password, $user->password)) {
                    $validator->errors()->add('password', __('users.password_not_different'));
                }

                if ($users->hasPendingEmailChange($user)) {
                    $validator->errors()->add('email', __('users.confirm_already_sent'));
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        // Не строку (?email[]=) отклонит правило
        if (is_string($email)) {
            $this->merge(['email' => mb_strtolower($email)]);
        }
    }
}
