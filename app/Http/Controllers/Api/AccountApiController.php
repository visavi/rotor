<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\BlackList;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\FileService;
use App\Services\UserService;
use App\Support\Validator;
use App\Traits\HandlesApiValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

/**
 * Свой аккаунт
 */
class AccountApiController extends Controller
{
    use HandlesApiValidation;

    /**
     * Изменение профиля
     */
    public function profile(Request $request, Validator $validator, UserService $userService): JsonResource
    {
        $user = getUser();

        $data = $userService->validateProfile($validator, $user, $request);

        $this->throwIfInvalid($validator);

        $userService->saveProfile($user, $data, $request);

        return UserProfileResource::make($user->fresh());
    }

    /**
     * Настройки аккаунта и доступные значения
     */
    public function settings(): JsonResponse
    {
        $user = getUser();

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
                'languages' => array_values(getAvailableLanguages()),
                'timezones' => range(-12, 12),
            ],
        ]);
    }

    /**
     * Изменение настроек
     */
    public function updateSettings(Request $request, Validator $validator, UserService $userService): JsonResponse
    {
        $user = getUser();

        $data = $userService->validateSettings($validator, $request);

        $this->throwIfInvalid($validator);

        $user->update($data);

        return response()->json(['message' => __('users.settings_success_changed')]);
    }

    /**
     * Смена пароля
     */
    public function password(Request $request, Validator $validator, UserService $userService): JsonResponse
    {
        $user = getUser();
        $newPassword = (string) $request->input('new_password');

        $userService->validatePassword(
            $validator,
            $user,
            $request->input('old_password'),
            $newPassword,
            $request->input('confirm_password'),
        );

        $this->throwIfInvalid($validator);

        $userService->changePassword($user, $newPassword);

        // Токен остаётся прежним: смена пароля не должна выкидывать приложение
        return response()->json(['message' => __('users.password_success_changed')]);
    }

    /**
     * Заявка на смену почты, подтверждение приходит письмом
     */
    public function email(Request $request, Validator $validator, UserService $userService): JsonResponse
    {
        $user = getUser();
        $email = strtolower((string) $request->input('email'));

        $userService->validateEmailChange($validator, $user, $email, $request->input('password'));

        $this->throwIfInvalid($validator);

        $userService->requestEmailChange($user, $email);

        return response()->json(['message' => __('users.confirm_success_sent')]);
    }

    /**
     * Повторная отправка письма с подтверждением регистрации
     */
    public function verify(Request $request, Validator $validator, CaptchaService $captcha): JsonResponse
    {
        $user = getUser();

        if (! setting('regkeys')) {
            abort(403, __('users.confirm_registration_disabled'));
        }

        if ($user->level !== User::PENDED) {
            abort(403, __('users.profile_not_confirmation'));
        }

        $email = strtolower((string) $request->input('email', $user->email));
        $domain = Str::substr(strrchr($email, '@') ?: '', 1);

        $validator
            ->true($captcha->verify($request), ['protect' => __('validator.captcha')])
            ->email($email, ['email' => __('validator.email')])
            ->false(
                User::query()->where('login', '<>', $user->login)->where('email', $email)->exists(),
                ['email' => __('users.email_already_exists')],
            )
            ->false(BlackList::isBlacklisted('email', $email), ['email' => __('users.email_is_blacklisted')])
            ->false(BlackList::isBlacklisted('domain', $domain), ['email' => __('users.domain_is_blacklisted')]);

        $this->throwIfInvalid($validator);

        $token = Str::random(32);

        $user->update([
            'email'         => $email,
            'confirm_token' => $token,
        ]);

        sendMail('mailer.register', [
            'to'         => $email,
            'subject'    => 'Регистрация на ' . setting('title'),
            'login'      => $user->login,
            'password'   => '*****',
            'confirmUrl' => route('confirm', ['token' => $token]),
        ]);

        return response()->json(['message' => __('users.confirm_code_success_sent')]);
    }

    /**
     * Смена статуса
     */
    public function status(Request $request, Validator $validator): JsonResponse
    {
        $user = getUser();

        $status = $request->input('status') ?: null;
        $cost = $status ? setting('editstatusmoney') : 0;

        $validator
            ->empty($user->ban, ['status' => __('users.status_changed_not_ban')])
            ->notEqual($status, $user->status, ['status' => __('users.status_different')])
            ->gte($user->point, setting('editstatuspoint'), ['status' => __('users.status_points')])
            ->gte($user->money, $cost, ['status' => __('users.status_moneys')])
            ->length($status, 3, 25, ['status' => __('users.status_short_or_long')], false);

        $this->throwIfInvalid($validator);

        $user->update([
            'status' => $status,
            'money'  => DB::raw('money - ' . $cost),
        ]);

        clearCache('status');

        return response()->json(['message' => __('users.status_success_changed')]);
    }

    /**
     * Смена цвета никнейма
     */
    public function color(Request $request, Validator $validator): JsonResponse
    {
        $user = getUser();

        $color = $request->input('color') ?: null;
        $cost = $color ? setting('editcolormoney') : 0;

        $validator
            ->notEqual($color, $user->color, ['color' => __('users.color_different')])
            ->gte($user->point, setting('editcolorpoint'), ['color' => __('users.color_points')])
            ->gte($user->money, $cost, ['color' => __('users.color_moneys')])
            ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

        $this->throwIfInvalid($validator);

        $user->update([
            'color' => $color,
            'money' => DB::raw('money - ' . $cost),
        ]);

        return response()->json(['message' => __('users.color_success_changed')]);
    }

    /**
     * Перегенерация или удаление токена
     */
    public function apikey(Request $request): JsonResponse
    {
        $user = getUser();

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
    public function photo(Request $request, ImageManager $imageManager): JsonResponse
    {
        $user = getUser();

        $request->validate([
            'photo' => [
                'required',
                'image',
                'max:' . FileService::maxFileSize(),
                'mimes:' . setting('media_extensions'),
                'dimensions:min_width=100,min_height=100',
            ],
        ]);

        $photo = $request->file('photo');

        if ($user->picture) {
            deleteFile(public_path($user->picture));
            deleteFile(public_path($user->avatar));
        }

        // Аватар — квадратная миниатюра фото, отдельно его не загружают
        $image = $imageManager->decode($photo);
        $image->coverDown(64, 64);
        $image->encodeUsingFormat(Format::PNG);

        $avatar = $user->uploadAvatarPath . '/' . uniqueName('png');
        $image->save(public_path($avatar));

        $file = $user->uploadFile($photo, false);

        $user->update([
            'picture' => $file['path'],
            'avatar'  => $avatar,
        ]);

        return response()->json([
            'message' => __('users.photo_success_uploaded'),
            'avatar'  => url($avatar),
            'picture' => url($file['path']),
        ]);
    }

    /**
     * Удаление фото и аватара
     */
    public function deletePhoto(): JsonResponse
    {
        $user = getUser();

        if (! $user->picture) {
            abort(422, __('users.photo_not_exist'));
        }

        deleteFile(public_path($user->picture));
        deleteFile(public_path($user->avatar));

        $user->update(['picture' => null, 'avatar' => null]);

        return response()->json(['message' => __('users.photo_success_deleted')]);
    }
}
