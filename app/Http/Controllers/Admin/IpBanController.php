<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IpBanController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(Request $request, Validator $validator): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $ip = $request->input('ip');

            $validator->ip($ip, ['ip' => __('admin.ipbans.ip_invalid')]);

            $duplicate = Ban::query()->where('ip', $ip)->first();
            $validator->empty($duplicate, ['ip' => __('admin.ipbans.ip_exists')]);

            if ($validator->isValid()) {
                Ban::query()->create([
                    'ip'         => $ip,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                clearCache('ipBan');

                setFlash('success', __('admin.ipbans.ip_success_added'));

                return redirect('admin/ipbans');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $logs = Ban::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('ipbanlist'));

        return view('admin/ipbans/index', compact('logs'));
    }

    /**
     * Удаление ip
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $del = intar($request->input('del'));

        $validator->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Ban::query()->whereIn('id', $del)->delete();
            clearCache('ipBan');

            setFlash('success', __('admin.ipbans.ip_selected_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/ipbans?page=' . $page);
    }

    /**
     * Очистка ip
     */
    public function clear(Validator $validator): RedirectResponse
    {
        $validator->true(isAdmin(User::BOSS), __('main.page_only_owner'));

        if ($validator->isValid()) {
            Ban::query()->truncate();
            clearCache('ipBan');

            setFlash('success', __('admin.ipbans.ip_success_cleared'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.ipbans.index');
    }
}
