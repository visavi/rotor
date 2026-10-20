<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
     */
    public function update(Request $request): RedirectResponse
    {
        $sets = $request->input('sets', []);

        if (empty($sets) || ! is_array($sets)) {
            return redirect()->back()
                ->with('danger', __('settings.settings_empty'));
        }

        // Не FormRequest: модули переопределяют update(Request) и сломались бы на новой сигнатуре.
        // Колонка value — string(255): длиннее строгий MySQL не пропустит
        $validator = Validator::make($request->only('sets'), [
            'sets.*' => ['nullable', 'max:255'],
        ], [
            'sets.*' => __('validator.text'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        foreach ($sets as $name => $value) {
            // Пустое поле приходит null, а колонка value NOT NULL
            Setting::query()->updateOrCreate(['name' => $name], ['value' => (string) $value]);
        }

        clearCache('settings');

        return redirect()->route($this->route)
            ->with('success', __('settings.settings_success_saved'));
    }
}
