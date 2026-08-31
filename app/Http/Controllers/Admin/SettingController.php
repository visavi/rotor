<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\UserService;
use App\Support\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(Request $request, Validator $validator): View|RedirectResponse
    {
        $act = $request->input('act', 'mains');

        if (! in_array($act, Setting::getActions(), true)) {
            abort(404, __('settings.page_invalid'));
        }

        if ($request->isMethod('post')) {
            $sets = $request->input('sets');
            $mods = $request->input('mods');
            $opt = $request->input('opt');

            $validator->notEmpty($sets, ['sets' => __('settings.settings_empty')]);

            foreach ((array) $sets as $name => $value) {
                if (empty($opt[$name]) || ! empty($value)) {
                    $validator->length($value, 1, 255, ['sets[' . $name . ']' => __('settings.field_required', ['field' => $name])]);
                }
            }

            if ($validator->isValid()) {
                foreach ($sets as $name => $value) {
                    if (isset($mods[$name])) {
                        $value *= $mods[$name];
                    }

                    Setting::query()->where('name', $name)->update(['value' => $value]);
                }

                clearCache('settings');

                return redirect('admin/settings?act=' . $act)
                    ->with('success', __('settings.settings_success_saved'));
            }

            return redirect('admin/settings?act=' . $act)
                ->withInput()
                ->withErrors($validator->getErrors());
        }

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

        return view('admin/settings/index', compact('settings', 'act', 'statsite', 'protects', 'slugs', 'emailModes'));
    }
}
