<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Models\Log;
use App\Models\User;

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
     */
    public function index()
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
     */
    public function clear()
    {
        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            Log::query()->truncate();

            setFlash('success', 'Лог-файл успешно очищен!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/logs');
    }
}
