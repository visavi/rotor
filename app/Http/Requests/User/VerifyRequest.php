<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Concerns\ChecksCaptcha;
use Illuminate\Foundation\Http\Attributes\RedirectToRoute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Повторное письмо подтверждения регистрации, можно на другой адрес
 */
#[RedirectToRoute('verify')]
class VerifyRequest extends FormRequest
{
    use ChecksCaptcha;

    public function rules(): array
    {
        return [
            'email' => UserRules::email(true, auth()->user()?->id),
        ];
    }

    public function messages(): array
    {
        return UserRules::messages();
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->checkCaptcha($validator),
        ];
    }

    protected function prepareForValidation(): void
    {
        // Приложение может не присылать адрес — письмо уходит на текущий
        $email = $this->input('email', auth()->user()?->email);

        if (is_string($email)) {
            $this->merge(['email' => mb_strtolower($email)]);
        }
    }
}
