<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IpBanController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View
     */
    public function index(Request $request, Validator $validator): View
    {
        if ($request->isMethod('post')) {
            $ip = $request->input('ip');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->ip($ip, ['ip' => __('admin.ipbans.ip_invalid')]);

            $duplicate = Ban::query()->where('ip', $ip)->first();
            $validator->empty($duplicate, ['ip' => __('admin.ipbans.ip_exists')]);

            if ($validator->isValid()) {
                Ban::query()->create([
                    'ip'         => $ip,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                ipBan(true);

                setFlash('success', __('admin.ipbans.ip_success_added'));
                redirect('/admin/ipbans');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $logs = Ban::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('ipbanlist'));

        return view('admin/ipbans/index', compact('logs'));
    }

    /**
     * Удаление ip
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page = int($request->input('page', 1));
        $del  = intar($request->input('del'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Ban::query()->whereIn('id', $del)->delete();
            ipBan(true);

            setFlash('success', __('admin.ipbans.ip_selected_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/ipbans?page=' . $page);
    }

    /**
     * Очистка ip
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function clear(Request $request, Validator $validator): void
    {
        $validator
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true(isAdmin(User::BOSS), __('main.page_only_owner'));

        if ($validator->isValid()) {
            Ban::query()->truncate();
            ipBan(true);

            setFlash('success', __('admin.ipbans.ip_success_cleared'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/ipbans');
    }
}
