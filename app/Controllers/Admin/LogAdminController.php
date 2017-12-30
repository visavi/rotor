<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Admlog;
use App\Models\Online;
use App\Models\User;

class LogAdminController extends AdminController
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
        $total = Admlog::query()->count();
        $page = paginate(setting('loglist'), $total);

        $logs = Admlog::query()
            ->orderBy('created_at', 'desc')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->with('user')
            ->get();


        return view('admin/logadmin/index', compact('logs', 'page'));
    }

    /**
     * Очистка логов
     */
    public function clear()
    {
        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            Admlog::query()->truncate();

            setFlash('success', 'Лог-файл успешно очищен!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/logadmin');
    }
}
