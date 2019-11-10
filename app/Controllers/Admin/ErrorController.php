<?php

declare(strict_types=1);

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

        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }

        $this->code  = int(request()->input('code', 404));
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

        $logs = Error::query()
            ->where('code', $code)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('loglist'))
            ->appends(['code' => $code]);

        return view('admin/errors/index', compact('logs', 'code', 'lists'));
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
            ->equal($token, $_SESSION['token'], __('validator.token'))
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
