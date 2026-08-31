<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\UserService;
use App\Support\Validator;
use App\Traits\HandlesApiValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Регистрация и восстановление доступа
 */
class AuthApiController extends Controller
{
    use HandlesApiValidation;

    /**
     * Защитная картинка для форм регистрации и восстановления
     */
    public function captcha(CaptchaService $captcha): JsonResponse
    {
        return response()->json($captcha->challenge());
    }

    /**
     * Регистрация
     */
    public function register(
        Request $request,
        Validator $validator,
        CaptchaService $captcha,
        UserService $userService,
    ): JsonResponse {
        if (! setting('openreg')) {
            abort(403, __('users.registration_suspended'));
        }

        $login = (string) $request->input('login');
        // При скрытом поле присланный адрес игнорируем — его легко подсунуть запросом
        $email = UserService::isEmailHidden()
            ? ''
            : strtolower((string) $request->input('email'));
        $gender = $request->input('gender') === User::MALE ? User::MALE : User::FEMALE;

        $validator->true($captcha->verify($request), ['protect' => __('validator.captcha')]);

        $userService->validateRegistration(
            $validator,
            $login,
            $request->input('password'),
            $request->input('password2', $request->input('password')),
            $email,
        );

        $this->throwIfInvalid($validator);

        $user = $userService->register($login, (string) $request->input('password'), $email, $gender);

        // Токен выдаётся сразу: иначе клиенту пришлось бы отдельно логиниться
        $user->update(['apikey' => Str::random(32)]);

        return response()->json([
            'message' => __('users.welcome', ['login' => $login]),
            'token'   => $user->apikey,
            // Пока аккаунт не подтверждён, писать на сайте нельзя
            'pending' => $user->level === User::PENDED,
            'user'    => UserProfileResource::make($user),
        ], 201);
    }

    /**
     * Восстановление пароля, ссылка уходит на почту
     */
    public function recovery(
        Request $request,
        Validator $validator,
        CaptchaService $captcha,
        UserService $userService,
    ): JsonResponse {
        $request->validate(['user' => ['required', 'string']]);

        $user = getUserByLoginOrEmail($request->input('user'));

        $validator->true($captcha->verify($request), ['protect' => __('validator.captcha')]);
        $validator->notEmpty($user, ['user' => __('validator.user')]);

        if ($user) {
            // Без почты письмо со ссылкой отправить некуда
            $validator->notEmpty($user->email, ['user' => __('users.email_not_attached')]);

            // Повторная заявка принимается только через час, как и на сайте
            $validator->false($userService->hasPendingRecovery($user), ['user' => __('mails.password_recovery_time')]);
        }

        $this->throwIfInvalid($validator);

        $userService->requestRecovery($user);

        return response()->json([
            'message' => __('mails.recovery_instructions', ['email' => hideMail($user->email)]),
        ]);
    }
}
