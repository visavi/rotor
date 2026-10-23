<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ColorRequest;
use App\Http\Requests\User\EmailChangeRequest;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\PhotoRequest;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\User\SettingsRequest;
use App\Http\Requests\User\StatusRequest;
use App\Http\Requests\User\VerifyRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use App\Services\UserService;
use App\Support\Locale;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Свой аккаунт
 */
class AccountApiController extends Controller
{
    /**
     * Изменение профиля
     */
    public function profile(ProfileRequest $request, UserService $userService, #[CurrentUser] User $user): JsonResource
    {
        $userService->saveProfile($user, $request->profile(), $request);

        return UserProfileResource::make($user->fresh());
    }

    /**
     * Настройки аккаунта и доступные значения
     */
    public function settings(#[CurrentUser] User $user): JsonResponse
    {
        return response()->json([
            'data' => [
                'themes'         => $user->themes,
                'language'       => $user->language,
                'timezone'       => $user->timezone,
                'notify_mention' => (bool) $user->notify_mention,
                'notify_reply'   => (bool) $user->notify_reply,
                'notify_comment' => (bool) $user->notify_comment,
                'subscribe'      => (bool) $user->subscribe,
            ],
            // Списки зависят от установленных тем и языков, клиенту их взять больше негде
            'available' => [
                'themes'    => array_values(getAvailableThemes()),
                'languages' => Locale::available(),
                'timezones' => range(-12, 14),
            ],
        ]);
    }

    /**
     * Изменение настроек
     */
    public function updateSettings(SettingsRequest $request, UserService $userService, #[CurrentUser] User $user): JsonResponse
    {
        $userService->saveSettings($user, $request->settings(), $request);

        return response()->json(['message' => __('users.settings_success_changed')]);
    }

    /**
     * Смена пароля
     */
    public function password(PasswordRequest $request, UserService $userService, #[CurrentUser] User $user): JsonResponse
    {
        $userService->changePassword($user, $request->validated('new_password'));

        // Токен остаётся прежним: смена пароля не должна выкидывать приложение
        return response()->json(['message' => __('users.password_success_changed')]);
    }

    /**
     * Заявка на смену почты, подтверждение приходит письмом
     */
    public function email(EmailChangeRequest $request, UserService $userService, #[CurrentUser] User $user): JsonResponse
    {
        $userService->requestEmailChange($user, $request->validated('email'));

        return response()->json(['message' => __('users.confirm_success_sent')]);
    }

    /**
     * Повторная отправка письма с подтверждением регистрации
     */
    public function verify(VerifyRequest $request, UserService $userService, #[CurrentUser] User $user): JsonResponse
    {
        if (! UserService::isEmailConfirm()) {
            abort(403, __('users.confirm_registration_disabled'));
        }

        if ($user->level !== User::PENDED) {
            abort(403, __('users.profile_not_confirmation'));
        }

        $userService->resendConfirmation($user, $request->validated('email'));

        return response()->json(['message' => __('users.confirm_code_success_sent')]);
    }

    /**
     * Смена статуса
     */
    public function status(StatusRequest $request): JsonResponse
    {
        $request->apply();

        clearCache('status');

        return response()->json(['message' => __('users.status_success_changed')]);
    }

    /**
     * Смена цвета никнейма
     */
    public function color(ColorRequest $request): JsonResponse
    {
        $request->apply();

        return response()->json(['message' => __('users.color_success_changed')]);
    }

    /**
     * Перегенерация или удаление токена
     */
    public function apikey(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $request->validate(['action' => ['nullable', 'string', 'in:create,change,delete']]);

        // Удаление токена закрывает доступ к API — текущий запрос последний
        $delete = $request->input('action') === 'delete';
        $apikey = $delete ? '' : Str::random(32);

        $user->update(['apikey' => $apikey]);

        return response()->json([
            'message' => $delete ? __('users.token_success_deleted') : __('users.token_success_changed'),
            'token'   => $apikey ?: null,
        ]);
    }

    /**
     * Загрузка фото и аватара
     */
    public function photo(PhotoRequest $request, UserService $userService, #[CurrentUser] User $user): JsonResponse
    {
        $userService->savePhoto($user, $request->file('photo'));

        return response()->json([
            'message' => __('users.photo_success_uploaded'),
            'avatar'  => url($user->avatar),
            'picture' => url($user->picture),
        ]);
    }

    /**
     * Удаление фото и аватара
     */
    public function deletePhoto(UserService $userService, #[CurrentUser] User $user): JsonResponse
    {
        if (! $user->picture) {
            abort(422, __('users.photo_not_exist'));
        }

        $userService->deletePhoto($user);

        return response()->json(['message' => __('users.photo_success_deleted')]);
    }
}
