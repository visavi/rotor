<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends AdminController
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        $logs = Log::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('loglist'));

        return view('admin/logs/index', compact('logs'));
    }

    /**
     * Очистка логов
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function clear(Request $request): RedirectResponse
    {
        if ($request->input('_token') === csrf_token()) {
            Log::query()->truncate();

            setFlash('success', __('admin.logs.success_cleared'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/logs');
    }
}
