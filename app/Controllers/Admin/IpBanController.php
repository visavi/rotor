<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Ban;
use App\Models\User;

class IpBanController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
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
        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $ip    = check(Request::input('ip'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->regex($ip, '|^[0-9]{1,3}\.[0-9,*]{1,3}\.[0-9,*]{1,3}\.[0-9,*]{1,3}$|', ['ip' => 'Вы ввели недопустимый IP-адрес для бана!']);

            $duplicate = Ban::query()->where('ip', $ip)->first();
            $validator->empty($duplicate, ['ip' => 'Введенный IP уже имеетеся в списке!']);

            if ($validator->isValid()) {

                Ban::query()->create([
                    'ip'         => $ip,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                ipBan(true);

                setFlash('success', 'IP успешно занесен в список!');
                redirect('/admin/ipbans');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Ban::query()->count();
        $page = paginate(setting('ipbanlist'), $total);

        $logs = Ban::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user')
            ->get();

        return view('admin/ipbans/index', compact('logs', 'page'));
    }

    /**
     * Удаление ip
     *
     * @return void
     */
    public function delete(): void
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные ip для удаления!');

        if ($validator->isValid()) {

            Ban::query()->whereIn('id', $del)->delete();
            ipBan(true);

            setFlash('success', 'Выбранные IP успешно удалены из списка!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/ipbans?page=' . $page);
    }

    /**
     * Очистка ip
     *
     * @return void
     */
    public function clear(): void
    {
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(isAdmin(User::BOSS), 'Очищать список IP может только владелец!');

        if ($validator->isValid()) {

            Ban::query()->truncate();
            ipBan(true);

            setFlash('success', 'Список IP успешно очищен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/ipbans');
    }
}
