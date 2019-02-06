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

        $request     = Request::createFromGlobals();
        $this->code  = int($request->input('code', 404));
        $this->lists = [403 => 'Ошибки 403', 404 => 'Ошибки 404', 405 => 'Ошибки 405', 666 => 'Автобаны'];

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
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function clear(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        $validator
            ->equal($token, $_SESSION['token'], trans('validator.token'))
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
