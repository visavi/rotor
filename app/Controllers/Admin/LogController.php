<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends AdminController
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
     * @return string
     */
    public function index(): string
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
     * @return void
     */
    public function clear(Request $request): void
    {
        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {
            Log::query()->truncate();

            setFlash('success', 'Лог-файл успешно очищен!');
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/logs');
    }
}
