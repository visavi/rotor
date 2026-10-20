<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Concerns\ChecksCaptcha;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Заявка на восстановление пароля: одни правила на сайт и API
 */
class RecoveryRequest extends FormRequest
{
    use ChecksCaptcha;

    private ?User $recoveryUser = null;

    public function authorize(): bool
    {
        return getUser() === null;
    }

    public function rules(): array
    {
        return [
            'user' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user' => __('validator.user'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->checkCaptcha($validator);

                if ($validator->errors()->has('user')) {
                    return;
                }

                $user = $this->recoveryUser();

                $error = match (true) {
                    ! $user => __('validator.user'),
                    // Без почты письмо со ссылкой отправить некуда
                    ! $user->email => __('users.email_not_attached'),
                    // Повторная заявка — только через час
                    app(UserService::class)->hasPendingRecovery($user) => __('mails.password_recovery_time'),
                    default                                            => null,
                };

                if ($error) {
                    $validator->errors()->add('user', $error);
                }
            },
        ];
    }

    /**
     * Пользователь по логину или почте
     */
    public function recoveryUser(): ?User
    {
        return $this->recoveryUser ??= getUserByLoginOrEmail($this->string('user')->value());
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException(__('main.already_authorized'));
    }
}
