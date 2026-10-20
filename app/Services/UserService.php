<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailChange;
use App\Models\PasswordReset;
use App\Models\User;
use App\Support\Registry;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

/**
 * Регистрация и доступы аккаунта
 *
 * Письма и сохранение одни на сайт и API. Правила — в App\Http\Requests\User,
 * тоже общие: разойдись они, требования к паролю в приложении отличались бы от сайта
 */
class UserService
{
    /** Поле почты при регистрации не показывается */
    public const string EMAIL_HIDDEN = 'hidden';

    /** Почту можно не указывать */
    public const string EMAIL_OPTIONAL = 'optional';

    /** Почта обязательна */
    public const string EMAIL_REQUIRED = 'required';

    /** Почта обязательна, аккаунт ждёт подтверждения по письму */
    public const string EMAIL_CONFIRM = 'confirm';

    public function __construct(
        private readonly MailService $mail,
        private readonly ImageManager $imageManager,
    ) {
        //
    }

    /**
     * Режим почты при регистрации
     */
    public static function emailMode(): string
    {
        $mode = (string) setting('email_mode');
        $modes = [self::EMAIL_HIDDEN, self::EMAIL_OPTIONAL, self::EMAIL_REQUIRED, self::EMAIL_CONFIRM];

        // Неизвестное значение считаем обязательной почтой — безопасный дефолт
        return in_array($mode, $modes, true) ? $mode : self::EMAIL_REQUIRED;
    }

    /**
     * Поле почты скрыто, аккаунт заводится без адреса
     */
    public static function isEmailHidden(): bool
    {
        return self::emailMode() === self::EMAIL_HIDDEN;
    }

    /**
     * Почта обязательна: подтверждение без адреса невозможно, поэтому входит сюда же
     */
    public static function isEmailRequired(): bool
    {
        return in_array(self::emailMode(), [self::EMAIL_REQUIRED, self::EMAIL_CONFIRM], true);
    }

    /**
     * Регистрацию нужно подтвердить письмом
     */
    public static function isEmailConfirm(): bool
    {
        return self::emailMode() === self::EMAIL_CONFIRM;
    }

    /**
     * Создание аккаунта с приветственным сообщением, письмом и полями модулей
     *
     * Хуки onRegisterSave здесь, а не в контроллерах: любая точка регистрации их не потеряет
     */
    public function register(string $login, string $password, string $email, string $gender, Request $request): User
    {
        // Ссылка подтверждения ведёт на сайт: письмо открывают в браузере, а не в приложении
        $confirmToken = self::isEmailConfirm() ? Str::random(32) : null;
        $confirmUrl = $confirmToken ? route('confirm', ['token' => $confirmToken]) : null;

        // Пустую строку в email хранить нельзя: уникальный индекс пропускает только NULL
        $user = User::query()->create([
            'login'         => $login,
            'password'      => Hash::make($password),
            'email'         => $email ?: null,
            'level'         => self::isEmailConfirm() ? User::PENDED : User::USER,
            'gender'        => $gender,
            'themes'        => setting('themes'),
            'point'         => 0,
            'language'      => setting('language'),
            'money'         => setting('registermoney'),
            'subscribe'     => Str::random(32),
            'confirm_token' => $confirmToken,
            'updated_at'    => now(),
        ]);

        $user->sendMessage(null, textNotice('register', ['username' => $login]));

        if ($email !== '') {
            $this->mail->queue('mailer.register', [
                'to'         => $email,
                'subject'    => 'Регистрация на ' . setting('title'),
                'login'      => $login,
                'password'   => $password,
                'confirmUrl' => $confirmUrl,
            ]);
        }

        foreach (Registry::$onRegisterSave as $handler) {
            $handler($user, $request);
        }

        return $user;
    }

    /**
     * Повторное письмо подтверждения регистрации, адрес можно сменить
     */
    public function resendConfirmation(User $user, string $email): void
    {
        $token = Str::random(32);

        $user->update([
            'email'         => $email,
            'confirm_token' => $token,
        ]);

        $this->mail->queue('mailer.register', [
            'to'         => $email,
            'subject'    => 'Регистрация на ' . setting('title'),
            'login'      => $user->login,
            'password'   => '*****',
            'confirmUrl' => route('confirm', ['token' => $token]),
        ]);
    }

    /**
     * Сохранение профиля с полями модулей
     *
     * @param array<string, mixed> $data
     */
    public function saveProfile(User $user, array $data, Request $request): void
    {
        $user->update($data);

        foreach (Registry::$onProfileSave as $handler) {
            $handler($user, $request);
        }
    }

    /**
     * Сохранение настроек с полями модулей
     *
     * @param array<string, mixed> $data
     */
    public function saveSettings(User $user, array $data, Request $request): void
    {
        $user->update($data);

        foreach (Registry::$onSettingsSave as $handler) {
            $handler($user, $request);
        }
    }

    /**
     * Смена пароля с уведомлением на почту
     */
    public function changePassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);

        if ($user->email) {
            $this->mail->queue('mailer.change_password', [
                'to'       => $user->email,
                'subject'  => 'Изменение пароля на ' . setting('title'),
                'username' => $user->getName(),
                'password' => $password,
            ]);
        }
    }

    /**
     * Загрузка фото профиля: старое фото удаляется, аватар — квадратная миниатюра нового
     */
    public function savePhoto(User $user, UploadedFile $photo): void
    {
        $this->deletePhotoFiles($user);

        $image = $this->imageManager->decode($photo);
        $image->coverDown(64, 64);
        $image->encodeUsingFormat(Format::PNG);

        // Каталог не хранится в репозитории, а $image->save() сам его не создаёт
        $avatar = $user->uploadAvatarPath . '/' . uniqueName('png');
        File::ensureDirectoryExists(public_path($user->uploadAvatarPath));
        $image->save(public_path($avatar));

        $file = $user->uploadFile($photo, false);

        $user->update([
            'picture' => $file['path'],
            'avatar'  => $avatar,
        ]);
    }

    /**
     * Удаление фото профиля и аватара
     */
    public function deletePhoto(User $user): void
    {
        $this->deletePhotoFiles($user);

        $user->update(['picture' => null, 'avatar' => null]);
    }

    /**
     * Есть ли незавершённая заявка на смену почты
     */
    public function hasPendingEmailChange(User $user): bool
    {
        EmailChange::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        return EmailChange::query()->where('user_id', $user->id)->exists();
    }

    /**
     * Заявка на смену почты, подтверждение уходит письмом
     */
    public function requestEmailChange(User $user, string $email): void
    {
        $token = Str::random(32);

        $this->mail->queue('mailer.change_mail', [
            'to'        => $email,
            'subject'   => 'Изменение email на ' . setting('title'),
            'username'  => $user->getName(),
            'changeUrl' => route('accounts.edit-mail', ['token' => $token]),
        ]);

        EmailChange::query()->create([
            'user_id'    => $user->id,
            'email'      => $email,
            'token'      => $token,
            'created_at' => now(),
        ]);
    }

    /**
     * Заявка на восстановление пароля, ссылка уходит письмом
     */
    public function requestRecovery(User $user): void
    {
        $token = Str::random(32);

        PasswordReset::query()->create([
            'email'      => $user->email,
            'token'      => $token,
            'created_at' => now(),
        ]);

        $this->mail->queue('mailer.recovery', [
            'to'       => $user->email,
            'subject'  => 'Восстановление пароля на ' . setting('title'),
            'username' => $user->getName(),
            'resetUrl' => route('restore', ['token' => $token]),
        ]);
    }

    /**
     * Есть ли незавершённая заявка на восстановление
     */
    public function hasPendingRecovery(User $user): bool
    {
        PasswordReset::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        return PasswordReset::query()->where('email', $user->email)->exists();
    }

    /**
     * Удаляет файлы фото и аватара, запись пользователя не трогает
     */
    private function deletePhotoFiles(User $user): void
    {
        if ($user->picture) {
            deleteFile(public_path($user->picture));
        }

        if ($user->avatar) {
            deleteFile(public_path($user->avatar));
        }
    }
}
