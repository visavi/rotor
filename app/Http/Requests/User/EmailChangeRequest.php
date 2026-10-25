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

                if ($this->filled('password') && ! Hash::check($this->string('password')->value(), $user->password)) {
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
        $this->merge(['email' => $this->string('email')->lower()->value()]);
    }
}
