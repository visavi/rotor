<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlackList;
use App\Models\EmailChange;
use App\Models\PasswordReset;
use App\Models\User;
use App\Support\Registry;
use App\Support\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Регистрация и доступы аккаунта
 *
 * Правила и письма одни на сайт и API: разойдись они, требования к паролю
 * в приложении отличались бы от сайта
 */
class UserService
{
    /**
     * Проверка данных регистрации
     */
    public function validateRegistration(
        Validator $validator,
        string $login,
        ?string $password,
        ?string $password2,
        string $email,
    ): void {
        $domain = Str::substr(strrchr($email, '@') ?: '', 1);

        $validator
            ->regex($login, '|^[a-z0-9\-]+$|i', ['login' => __('validator.login')])
            ->regex(Str::substr($login, 0, 1), '|^[a-z0-9]+$|i', ['login' => __('users.login_begin_requirements')])
            ->email($email, ['email' => __('validator.email')])
            ->length($login, 3, 20, ['login' => __('users.login_length_requirements')])
            ->length($password, 6, 20, ['password' => __('users.password_length_requirements')])
            ->equal($password, $password2, ['password2' => __('users.passwords_different')])
            ->false(ctype_digit($login), ['login' => __('users.field_characters_requirements')])
            ->false(ctype_digit($password), ['password' => __('users.field_characters_requirements')])
            ->false(substr_count($login, '-') > 2, ['login' => __('users.login_hyphens_requirements')]);

        if ($login !== '') {
            $checkLogin = User::query()->where('login', $login)->exists();
            $validator->false($checkLogin, ['login' => __('users.login_already_exists')]);

            $validator->false(BlackList::isBlacklisted('login', strtolower($login)), ['login' => __('users.login_is_blacklisted')]);
        }

        $checkMail = User::query()->where('email', $email)->exists();
        $validator->false($checkMail, ['email' => __('users.email_already_exists')]);

        $validator
            ->false(BlackList::isBlacklisted('domain', $domain), ['email' => __('users.domain_is_blacklisted')])
            ->false(BlackList::isBlacklisted('email', $email), ['email' => __('users.email_is_blacklisted')]);
    }

    /**
     * Создание аккаунта с приветственным сообщением и письмом
     */
    public function register(string $login, string $password, string $email, string $gender): User
    {
        // Ссылка подтверждения ведёт на сайт: письмо открывают в браузере, а не в приложении
        $confirmToken = setting('regkeys') ? Str::random(32) : null;
        $confirmUrl = $confirmToken ? route('confirm', ['token' => $confirmToken]) : null;

        $user = User::query()->create([
            'login'         => $login,
            'password'      => Hash::make($password),
            'email'         => $email,
            'level'         => setting('regkeys') ? User::PENDED : User::USER,
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

        sendMail('mailer.register', [
            'to'         => $email,
            'subject'    => 'Регистрация на ' . setting('title'),
            'login'      => $login,
            'password'   => $password,
            'confirmUrl' => $confirmUrl,
        ]);

        return $user;
    }

    /**
     * Проверка и разбор профиля
     *
     * @return array<string, mixed> поля для сохранения
     */
    public function validateProfile(Validator $validator, User $user, Request $request): array
    {
        $info = $request->input('info');
        $name = $request->input('name');
        $site = $request->input('site');
        $birthday = $request->input('birthday');
        $phone = preg_replace('/[^\d+]/', '', (string) $request->input('phone'));

        $validator
            ->url($site, ['site' => __('validator.site')], false)
            ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => __('validator.date')], false)
            ->phone($phone, ['phone' => __('validator.phone')], false)
            ->length($info, 0, 1000, ['info' => __('users.info_yourself_long')])
            ->length($name, 3, 20, ['name' => __('users.name_short_or_long')], false);

        foreach (Registry::$onProfileValidate as $handler) {
            $handler($user, $request, $validator, true);
        }

        return [
            'name'     => $name,
            'gender'   => $request->input('gender') === User::MALE ? User::MALE : User::FEMALE,
            'country'  => Str::substr((string) $request->input('country'), 0, 30),
            'city'     => Str::substr((string) $request->input('city'), 0, 50),
            'phone'    => $phone,
            'site'     => $site,
            'birthday' => $birthday,
            'info'     => $info,
        ];
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
     * Проверка и разбор настроек
     *
     * @return array<string, mixed> поля для сохранения
     */
    public function validateSettings(Validator $validator, Request $request): array
    {
        $themes = $request->input('themes');
        $timezone = $request->input('timezone', 0);
        $language = $request->input('language');

        $validator
            ->regex($themes, '|^[a-z0-9_\-]+$|i', ['themes' => __('users.theme_invalid')])
            ->true(in_array($themes, getAvailableThemes(), true) || empty($themes), ['themes' => __('users.theme_not_installed')])
            ->regex($language, '|^[a-z]+$|', ['language' => __('users.language_invalid')])
            ->in($language, getAvailableLanguages(), ['language' => __('users.language_not_installed')])
            ->regex($timezone, '|^[\-\+]{0,1}[0-9]{1,2}$|', ['timezone' => __('users.timezone_invalid')]);

        return [
            'themes'         => $themes,
            'timezone'       => $timezone,
            'notify_mention' => $request->input('notify_mention') ? 1 : 0,
            'notify_reply'   => $request->input('notify_reply') ? 1 : 0,
            'notify_comment' => $request->input('notify_comment') ? 1 : 0,
            // Подписка хранится ключом рассылки, отказ его стирает
            'subscribe' => $request->input('subscribe') ? Str::random(32) : null,
            'language'  => $language,
        ];
    }

    /**
     * Проверка смены пароля
     */
    public function validatePassword(
        Validator $validator,
        User $user,
        ?string $oldPassword,
        ?string $newPassword,
        ?string $confirmPassword,
    ): void {
        $validator
            ->true(Hash::check((string) $oldPassword, $user->password), ['old_password' => __('users.password_not_different')])
            ->false(Hash::check((string) $newPassword, $user->password), ['old_password' => __('users.password_different')])
            ->length($newPassword, 6, 20, ['new_password' => __('users.password_length_requirements')])
            ->notEqual($user->login, $newPassword, ['new_password' => __('users.login_different')])
            ->equal($newPassword, $confirmPassword, ['confirm_password' => __('users.passwords_different')]);

        if (ctype_digit((string) $newPassword)) {
            $validator->addError(['new_password' => __('users.field_characters_requirements')]);
        }
    }

    /**
     * Смена пароля с уведомлением на почту
     */
    public function changePassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);

        sendMail('mailer.change_password', [
            'to'       => $user->email,
            'subject'  => 'Изменение пароля на ' . setting('title'),
            'username' => $user->getName(),
            'password' => $password,
        ]);
    }

    /**
     * Проверка смены почты
     */
    public function validateEmailChange(Validator $validator, User $user, string $email, ?string $password): void
    {
        $validator
            ->notEqual($email, $user->email, ['email' => __('users.email_different')])
            ->email($email, ['email' => __('validator.email')])
            ->true(Hash::check((string) $password, $user->password), ['password' => __('users.password_not_different')]);

        $validator->false(User::query()->where('email', $email)->exists(), ['email' => __('users.email_already_exists')]);
        $validator->false(BlackList::isBlacklisted('email', $email), ['email' => __('users.email_is_blacklisted')]);

        EmailChange::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        $emailChange = EmailChange::query()->where('user_id', $user->id)->first();
        $validator->empty($emailChange, __('users.confirm_already_sent'));
    }

    /**
     * Заявка на смену почты, подтверждение уходит письмом
     */
    public function requestEmailChange(User $user, string $email): void
    {
        $token = Str::random(32);

        sendMail('mailer.change_mail', [
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

        sendMail('mailer.recovery', [
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
}
