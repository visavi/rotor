<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class ModuleSettingController extends AdminController
{
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

        if (empty($sets)) {
            setFlash('danger', __('settings.settings_empty'));

            return redirect()->back();
        }

        foreach ($sets as $name => $value) {
            Setting::query()->updateOrCreate(['name' => $name], ['value' => $value]);
        }

        clearCache('settings');
        setFlash('success', __('settings.settings_success_saved'));

        return redirect()->route($this->route);
    }
}
