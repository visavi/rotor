<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Setting;
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

            $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
                ->notEmpty($sets, ['sets' => __('settings.settings_empty')]);

            foreach ($sets as $name => $value) {
                if (empty($opt[$name]) || ! empty($value)) {
                    $validator->length($sets[$name], 1, 255, ['sets[' . $name . ']' => __('settings.field_required', ['field' => $name])]);
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
                setFlash('success', __('settings.settings_success_saved'));

                return redirect('admin/settings?act=' . $act);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $counters = [
            __('main.disable'),
            __('settings.hosts_hosts_all'),
            __('settings.hits_hits_all'),
            __('settings.hits_hosts'),
            __('settings.hits_all_hosts_all'),
            __('settings.graphical'),
        ];

        $statsite = [
            __('settings.site_open'),
            __('settings.site_closed_guest'),
            __('settings.site_closed_all'),
        ];

        $protects = [
            'graphical'    => __('settings.graphical'),
            'animated'     => __('settings.animated'),
            'recaptcha_v2' => 'Recaptcha v2',
            'recaptcha_v3' => 'Recaptcha v3',
        ];

        $settings = Setting::query()->pluck('value', 'name')->all();

        return view('admin/settings/index', compact('settings', 'act', 'counters', 'statsite', 'protects'));
    }
}
