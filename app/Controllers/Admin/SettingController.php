<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        $act = check($request->input('act', 'mains'));

        if (! in_array($act, Setting::getActions(), true)) {
            abort(404, __('settings.page_invalid'));
        }

        if ($request->isMethod('post')) {
            $sets  = check($request->input('sets'));
            $mods  = check($request->input('mods'));
            $opt   = check($request->input('opt'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->notEmpty($sets, ['sets' => __('settings.settings_empty')]);

            foreach ($sets as $name => $value) {
                if (empty($opt[$name]) || ! empty($sets[$name])) {
                    $validator->length($sets[$name], 1, 255, ['sets[' . $name . ']' => __('settings.field_required', ['field' => check($name)])]);
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
                redirect('/admin/settings?act=' . $act);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
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
            'graphical'    =>  __('settings.graphical'),
            'recaptcha_v2' => 'Recaptcha v2',
            'recaptcha_v3' => 'Recaptcha v3',
        ];

        $settings = Setting::query()->pluck('value', 'name')->all();

        return view('admin/settings/index', compact('settings', 'act', 'counters', 'statsite', 'protects'));
    }
}
