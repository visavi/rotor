<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Error;
use App\Models\User;
use Illuminate\Http\Request;

class ErrorController extends AdminController
{
    /**
     * @var array
     */
    private $lists;

    /**
     * @var int
     */
    private $code;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $this->code  = int($request->input('code', 404));
        $this->lists = [404 => 'Ошибки 404', 403 => 'Ошибки 403', 666 => 'Автобаны'];

        if (! isset($this->lists[$this->code])) {
            abort(404, 'Указанный лог-файл не существует!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $lists = $this->lists;
        $code  = $this->code;

        $total = Error::query()->where('code', $code)->count();
        $page = paginate(setting('loglist'), $total);

        $logs = Error::query()
            ->where('code', $code)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('admin/errors/index', compact('logs', 'page', 'code', 'lists'));
    }

    /**
     * Очистка логов
     *
     * @return void
     */
    public function clear(): void
    {
        $token = check($request->input('token'));

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(isAdmin(User::BOSS), 'Очищать логи может только владелец!');

        if ($validator->isValid()) {

            Error::query()->truncate();

            setFlash('success', 'Логи успешно очищены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/errors');
    }
}
