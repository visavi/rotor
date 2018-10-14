<?php

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
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $total = Log::query()->count();
        $page = paginate(setting('loglist'), $total);

        $logs = Log::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user')
            ->get();

        return view('admin/logs/index', compact('logs', 'page'));
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
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/logs');
    }
}
