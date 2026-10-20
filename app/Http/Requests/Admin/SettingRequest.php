<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Сохранение настроек ядра
 *
 * Поле обязательно, если форма не пометила его opt[name]; поле с множителем
 * mods[name] (мегабайты в байты, минуты в секунды) должно быть числом.
 * Без string: число из JSON — тоже значение, max без numeric считает его длину
 */
class SettingRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'sets'   => ['required', 'array'],
            'mods.*' => ['numeric'],
        ];

        // С numeric правило max сравнивало бы само число, а не длину строки:
        // поле с множителем проверяется только как число, остальные — по длине колонки
        foreach (array_keys((array) $this->input('sets')) as $name) {
            $rules['sets.' . $name] = [
                $this->input('opt.' . $name) ? 'nullable' : 'required',
                $this->filled('mods.' . $name) ? 'numeric' : 'max:255',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = ['sets' => __('settings.settings_empty')];

        foreach (array_keys((array) $this->input('sets')) as $name) {
            $messages['sets.' . $name] = __('settings.field_required', ['field' => $name]);
        }

        return $messages;
    }

    /**
     * Возвращает настройки к записи: множители применены, пустое — пустой строкой
     *
     * @return array<string, string>
     */
    public function settings(): array
    {
        $mods = (array) $this->input('mods');

        return collect($this->validated('sets'))
            ->map(static fn (mixed $value, int|string $name) => (string) (isset($mods[$name]) ? $value * $mods[$name] : $value))
            ->all();
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.settings', ['act' => $this->input('act', 'mains')]);
    }
}
