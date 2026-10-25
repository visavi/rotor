<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DeleteSelectedRequest;
use App\Http\Requests\Admin\IpBanRequest;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IpBanController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $logs = Ban::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('ipbanlist'));

        return view('admin/ipbans/index', compact('logs'));
    }

    /**
     * Добавление ip
     */
    public function store(IpBanRequest $request): RedirectResponse
    {
        Ban::query()->create([
            'ip'         => $request->validated('ip'),
            'user_id'    => auth()->id(),
            'created_at' => now(),
        ]);

        return redirect('admin/ipbans')
            ->with('success', __('admin.ipbans.ip_success_added'));
    }

    /**
     * Удаление ip
     */
    public function delete(DeleteSelectedRequest $request): RedirectResponse
    {
        $page = $request->integer('page', 1);

        Ban::query()->whereIn('id', $request->ids())->delete();
        clearCache('ipBan');

        return redirect('admin/ipbans?page=' . $page)
            ->with('success', __('admin.ipbans.ip_selected_deleted'));
    }

    /**
     * Очистка ip
     */
    public function clear(): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            return redirect()->route('admin.ipbans.index')
                ->withErrors(__('main.page_only_owner'));
        }

        Ban::query()->truncate();
        clearCache('ipBan');

        return redirect()->route('admin.ipbans.index')
            ->with('success', __('admin.ipbans.ip_success_cleared'));
    }
}
