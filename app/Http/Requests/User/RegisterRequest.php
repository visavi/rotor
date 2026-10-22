<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Concerns\ChecksCaptcha;
use App\Models\User;
use App\Services\UserService;
use App\Support\Registry;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Регистрация: одни правила на сайт и API
 */
class RegisterRequest extends FormRequest
{
    use ChecksCaptcha;

    /**
     * Регистрация открыта, и пользователь ещё не вошёл
     */
    public function authorize(): Response
    {
        if (getUser()) {
            return Response::deny(__('users.already_registered'));
        }

        return setting('openreg') ? Response::allow() : Response::deny(__('users.registration_suspended'));
    }

    public function rules(): array
    {
        return [
            'login'     => UserRules::login(),
            'password'  => UserRules::password(),
            'password2' => ['required', 'same:password'],
            'email'     => UserRules::email(UserService::isEmailRequired()),
        ];
    }

    public function messages(): array
    {
        return [
            ...UserRules::messages(),
            ...UserRules::passwordMessages('password'),
            'password2' => __('users.passwords_different'),
        ];
    }

    /**
     * Капча и поля модулей
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->checkCaptcha($validator);

                foreach (Registry::$onRegisterValidate as $handler) {
                    $handler($this, $validator);
                }
            },
        ];
    }

    /**
     * Почта в нижнем регистре, пустая — пустой строкой
     */
    public function email(): string
    {
        return (string) $this->validated('email');
    }

    public function gender(): string
    {
        return $this->input('gender') === User::MALE ? User::MALE : User::FEMALE;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // При скрытом поле присланный адрес игнорируем — его легко подсунуть запросом
            'email' => UserService::isEmailHidden() ? null : ($this->filled('email') ? $this->string('email')->lower()->value() : null),
            // Приложение повтор пароля не присылает
            'password2' => $this->input('password2', $this->input('password')),
        ]);
    }
}
