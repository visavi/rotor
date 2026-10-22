<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class ModuleSettingController extends AdminController
{
    /**
     * Настройки модулей — только для владельца, как и настройки ядра
     *
     * Проверка здесь, а не в маршрутах: модуль мог открыть страницу уровню admin,
     * а update() сохраняет любые ключи из sets[] — в том числе настройки ядра.
     * Через getMiddleware(), а не конструктор: модуль со своим __construct её не потеряет
     */
    public function getMiddleware(): array
    {
        return [
            ...parent::getMiddleware(),
            ['middleware' => 'check.admin:boss', 'options' => []],
        ];
    }

    /**
     * Шаблон страницы настроек
     */
    protected string $view;

    /**
     * Имя роута для редиректа после сохранения
     */
    protected string $route;

    /**
     * Настройки
     */
    public function index(): View
    {
        $settings = Setting::query()->pluck('value', 'name')->all();

        return view($this->view, compact('settings'));
    }

    /**
     * Сохранение настроек
     *
     * Модуль с правилами сохраняет только свои ключи из rules():
     * чужие имена из формы, в том числе настройки ядра, в базу не попадают.
     * Сигнатуру не менять: сторонние модули переопределяют update(Request)
     */
    public function update(Request $request): RedirectResponse
    {
        $sets = $request->input('sets', []);

        if (empty($sets) || ! is_array($sets)) {
            return redirect()->route($this->route)
                ->with('danger', __('settings.settings_empty'));
        }

        // Колонка value — string(255): длиннее строгий MySQL не пропустит.
        // Правило модуля для ключа заменяет общее sets.*, поэтому длина добавляется
        // к каждому замыканием: max:255 у числового поля сравнивал бы значение, а не длину
        $length = static function (string $attribute, mixed $value, Closure $fail): void {
            if (is_scalar($value) && mb_strlen((string) $value) > 255) {
                $fail(__('validator.text'));
            }
        };

        $rules = array_map(
            static fn (mixed $rule): array => [...(is_array($rule) ? $rule : explode('|', $rule)), $length],
            $this->rules(),
        );

        $validator = Validator::make($request->only('sets'), [
            'sets.*' => ['nullable', $length],
            ...$rules,
        ], $this->messages());

        if ($validator->fails()) {
            return redirect()->route($this->route)
                ->withInput()
                ->withErrors($validator);
        }

        if ($this->rules()) {
            $keys = array_map(static fn (string $key): string => Str::after($key, 'sets.'), array_keys($this->rules()));
            $sets = Arr::only($sets, $keys);
        }

        foreach ($sets as $name => $value) {
            // Пустое поле приходит null, а колонка value NOT NULL
            Setting::query()->updateOrCreate(['name' => $name], ['value' => (string) $value]);
        }

        clearCache('settings');

        return redirect()->route($this->route)
            ->with('success', __('settings.settings_success_saved'));
    }

    /**
     * Правила настроек модуля: ключи вида sets.имя
     *
     * Пустой список — сохраняются все поля формы без своей проверки
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Сообщения к правилам настроек
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [];
    }
}
