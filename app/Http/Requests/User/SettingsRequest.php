<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Support\Locale;
use App\Support\Registry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Настройки аккаунта: одни правила на сайт и API
 */
class SettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'themes'   => ['required', 'string', Rule::in(getAvailableThemes())],
            'language' => ['required', 'string', Rule::in(Locale::available())],
            // Реальные смещения — от UTC−12 до UTC+14 (Тонга, Кирибати)
            'timezone' => ['nullable', 'integer', 'between:-12,14'],
        ];
    }

    public function messages(): array
    {
        return [
            'themes.required'   => __('users.theme_invalid'),
            'themes'            => __('users.theme_not_installed'),
            'language.required' => __('users.language_invalid'),
            'language'          => __('users.language_not_installed'),
            'timezone'          => __('users.timezone_invalid'),
        ];
    }

    /**
     * Настройки модулей
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach (Registry::$onSettingsValidate as $handler) {
                    $handler(auth()->user(), $this, $validator);
                }
            },
        ];
    }

    /**
     * Настройки к сохранению
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return [
            'themes'         => $this->validated('themes'),
            'timezone'       => (string) ($this->validated('timezone') ?? 0),
            'notify_mention' => $this->boolean('notify_mention') ? 1 : 0,
            'notify_reply'   => $this->boolean('notify_reply') ? 1 : 0,
            'notify_comment' => $this->boolean('notify_comment') ? 1 : 0,
            'language'       => $this->validated('language'),
        ];
    }
}
