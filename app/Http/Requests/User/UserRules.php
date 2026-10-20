<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use App\Rules\EmailRule;
use App\Rules\LoginRule;
use App\Rules\NotBlacklistedRule;
use App\Rules\PhoneRule;
use App\Rules\TextLengthRule;
use Illuminate\Validation\Rule;

/**
 * Правила полей аккаунта, общие для сайта, API и админки
 */
final class UserRules
{
    /**
     * Новый логин: формат, занятость, чёрный список
     */
    public static function login(): array
    {
        return [
            'required',
            'string',
            new LoginRule(),
            Rule::unique(User::class, 'login'),
            new NotBlacklistedRule('login'),
        ];
    }

    /**
     * Почта: формат, занятость (кроме своей), чёрные списки адресов и доменов
     *
     * Администратор чёрные списки не проверяет — $blacklist = false
     */
    public static function email(bool $required, ?int $ignoreId = null, bool $blacklist = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:100',
            new EmailRule(),
            Rule::unique(User::class, 'email')->ignore($ignoreId),
            ...($blacklist ? [new NotBlacklistedRule('email'), new NotBlacklistedRule('domain')] : []),
        ];
    }

    /**
     * Новый пароль: 6–20 символов, не только цифры
     */
    public static function password(): array
    {
        return ['required', 'string', 'between:6,20', 'not_regex:/^\d+$/'];
    }

    /**
     * Поля анкеты, которые заполняет и пользователь, и администратор
     */
    public static function profile(): array
    {
        return [
            'phone'    => ['nullable', new PhoneRule()],
            'site'     => ['nullable', 'string', 'max:50', 'url:http,https'],
            'birthday' => ['nullable', 'date_format:d.m.Y'],
            'info'     => ['nullable', 'string', new TextLengthRule(0, 1000)],
        ];
    }

    /**
     * Сообщения к правилам логина, почты и анкеты
     *
     * У почты нет общего ключа: свои тексты у EmailRule и чёрного списка
     */
    public static function messages(): array
    {
        return [
            'login.required' => __('validator.login'),
            'login.string'   => __('validator.login'),
            'login.unique'   => __('users.login_already_exists'),
            'email.required' => __('validator.email'),
            'email.string'   => __('validator.email'),
            'email.max'      => __('validator.email'),
            'email.unique'   => __('users.email_already_exists'),
            'phone'          => __('validator.phone'),
            'site'           => __('validator.site'),
            'birthday'       => __('validator.date'),
            'info'           => __('users.info_yourself_long'),
        ];
    }

    /**
     * Сообщения к правилам пароля
     *
     * Поле называется по-разному: password при регистрации, new_password при смене
     */
    public static function passwordMessages(string $field): array
    {
        return [
            $field . '.not_regex' => __('users.field_characters_requirements'),
            $field                => __('users.password_length_requirements'),
        ];
    }

    /**
     * Телефон хранится цифрами с необязательным +
     */
    public static function normalizePhone(?string $phone): ?string
    {
        return $phone === null ? null : preg_replace('/[^\d+]/', '', $phone);
    }
}
