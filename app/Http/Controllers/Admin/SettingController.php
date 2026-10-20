<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use App\Services\UserService;
use App\Support\Registry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(Request $request): View
    {
        $act = $this->action($request);

        $statsite = [
            __('settings.site_open'),
            __('settings.site_closed_guest'),
            __('settings.site_closed_all'),
        ];

        $protects = [
            'disable'      => __('main.disable'),
            'graphical'    => __('settings.graphical'),
            'animated'     => __('settings.animated'),
            'recaptcha_v2' => 'Recaptcha v2',
            'recaptcha_v3' => 'Recaptcha v3',
        ];

        $emailModes = [
            UserService::EMAIL_HIDDEN   => __('settings.email_mode_hidden'),
            UserService::EMAIL_OPTIONAL => __('settings.email_mode_optional'),
            UserService::EMAIL_REQUIRED => __('settings.email_mode_required'),
            UserService::EMAIL_CONFIRM  => __('settings.email_mode_confirm'),
        ];

        $slugs = [
            '%id%'             => 'id',
            '%id%-%slug%'      => 'id-slug',
            '%id%-%slug%.html' => 'id-slug.html',
        ];

        $settings = Setting::query()->pluck('value', 'name')->all();

        $homepages = ['feed' => __('settings.homepage_feed')];
        foreach (Registry::$homepages as $key => $homepage) {
            $homepages[$key] = __($homepage['label']);
        }

        // Модуль выбранной главной выключен: пункт остаётся, иначе сохранение
        // других настроек молча переключит главную на ленту
        $current = $settings['homepage'] ?? 'feed';
        if (! isset($homepages[$current])) {
            $homepages[$current] = __('settings.homepage_unavailable', ['name' => $current]);
        }

        return view('admin/settings/index', compact('settings', 'act', 'statsite', 'protects', 'slugs', 'emailModes', 'homepages'));
    }

    /**
     * Сохранение настроек
     */
    public function update(SettingRequest $request): RedirectResponse
    {
        $act = $this->action($request);

        foreach ($request->settings() as $name => $value) {
            Setting::query()->where('name', $name)->update(['value' => $value]);
        }

        clearCache('settings');

        return redirect()->route('admin.settings', ['act' => $act])
            ->with('success', __('settings.settings_success_saved'));
    }

    /**
     * Возвращает раздел настроек из ?act= или 404
     */
    private function action(Request $request): string
    {
        $act = $request->string('act', 'mains')->value();

        if (! in_array($act, Setting::getActions(), true)) {
            abort(404, __('settings.page_invalid'));
        }

        return $act;
    }
}
