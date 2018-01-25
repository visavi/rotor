<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;

class DelUserController extends AdminController
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
        $users  = collect();
        $period = check(Request::input('period'));
        $point  = check(Request::input('point'));

        if (Request::isMethod('post')) {

            if ($period < 180) {
                abort('default', 'Указанно недопустимое время для удаления!');
            }

            $users = User::query()
                ->where('updated_at', '<', SITETIME - ($period * 24 * 3600))
                ->where('point', '<=', $point)
                ->get();

            if ($users->isEmpty()) {
                abort('default', 'Отсутствуют пользователи для удаления!');
            }
        }

        $total = User::query()->count();

        return view('admin/deluser/index', compact('users', 'total', 'period', 'point'));
    }

    /**
     * Очистка пользователей
     */
    public function clear()
    {
        $token  = check(Request::input('token'));
        $period = check(Request::input('period'));
        $point  = check(Request::input('point'));

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->gte($period, 180, 'Указанно недопустимое время для удаления!');

        $users = User::query()
            ->where('updated_at', '<', SITETIME - ($period * 24 * 3600))
            ->where('point', '<=', $point)
            ->get();

        $validator->true($users->isNotEmpty(), 'Отсутствуют пользователи для удаления!');

        if ($validator->isValid()) {

            foreach ($users as $user) {
                deleteAlbum($user);
                deleteUser($user);
            }

            setFlash('success', 'Пользователи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/delusers');
    }
}
