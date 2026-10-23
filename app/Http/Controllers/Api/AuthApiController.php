<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RecoveryRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Регистрация и восстановление доступа
 */
class AuthApiController extends Controller
{
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
    public function register(RegisterRequest $request, UserService $userService): JsonResponse
    {
        $login = $request->validated('login');

        $user = $userService->register($login, $request->validated('password'), $request->email(), $request->gender(), $request);

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
    public function recovery(RecoveryRequest $request, UserService $userService): JsonResponse
    {
        $user = $request->recoveryUser();

        $userService->requestRecovery($user);

        return response()->json([
            'message' => __('mails.recovery_instructions', ['email' => hideMail((string) $user->email)]),
        ]);
    }
}
