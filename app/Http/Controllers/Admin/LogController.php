<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LogController extends AdminController
{
    /**
     * Главная страница
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
     */
    public function clear(): RedirectResponse
    {
        Log::query()->truncate();

        setFlash('success', __('admin.logs.success_cleared'));

        return redirect()->route('admin.logs.index');
    }
}
