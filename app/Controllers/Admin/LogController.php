<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
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

        if (! isAdmin('admin')) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $code = abs(intval(Request::input('code', '404')));
        $list = [404 => 'Ошибки 404', 403 => 'Ошибки 403', 666 => 'Автобаны'];

        if (! array_key_exists($code, $list)) {
            abort('default', 'Указанный лог-файл не существует!');
        }

        $total = Log::query()->where('code', $code)->count();
        $page = paginate(setting('loglist'), $total);

        $logs = Log::query()
            ->where('code', $code)
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('admin/log/index', compact('logs', 'page', 'code', 'list'));
    }

    /**
     * Очистка логов
     */
    public function clear()
    {
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(isAdmin('boss'), 'Очищать логи может только владелец!');

        if ($validator->isValid()) {

            Log::query()->truncate();

            setFlash('success', 'Логи успешно очищены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/log');
    }
}
