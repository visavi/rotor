<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Ban;
use App\Models\User;
use App\Support\Validator;
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
                    'created_at' => now(),
                ]);

                clearCache('ipBan');

                return redirect('admin/ipbans')
                    ->with('success', __('admin.ipbans.ip_success_added'));
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($validator->getErrors());
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

        $redirect = 'admin/ipbans?page=' . $page;

        if (! $validator->isValid()) {
            return redirect($redirect)
                ->withErrors($validator->getErrors());
        }

        Ban::query()->whereIn('id', $del)->delete();
        clearCache('ipBan');

        return redirect($redirect)
            ->with('success', __('admin.ipbans.ip_selected_deleted'));
    }

    /**
     * Очистка ip
     */
    public function clear(Validator $validator): RedirectResponse
    {
        $validator->true(isAdmin(User::BOSS), __('main.page_only_owner'));

        if (! $validator->isValid()) {
            return redirect()->route('admin.ipbans.index')
                ->withErrors($validator->getErrors());
        }

        Ban::query()->truncate();
        clearCache('ipBan');

        return redirect()->route('admin.ipbans.index')
            ->with('success', __('admin.ipbans.ip_success_cleared'));
    }
}
