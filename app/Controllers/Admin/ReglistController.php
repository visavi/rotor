<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;

class ReglistController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort('403', 'Доступ запрещен!');
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
            $page   = int(Request::input('page', 1));
            $token  = check(Request::input('token'));
            $choice = intar(Request::input('choice'));
            $action = check(Request::input('action'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->notEmpty($choice, 'Отсутствуют выбранные пользователи!')
                ->in($action, ['yes', 'no'], ['action' => 'Необходимо выбрать действие!']);

            if ($validator->isValid()) {

                if ($action === 'yes') {

                    User::query()
                        ->whereIn('id', $choice)
                        ->update([
                            'level' => User::USER
                        ]);

                    setFlash('success', 'Выбранные пользователи успешно одобрены!');
                } else {

                    $users = User::query()
                        ->whereIn('id', $choice)
                        ->get();

                    foreach ($users as $user) {
                        $user->delete();
                    }

                    setFlash('success', 'Выбранные пользователи успешно удалены!');
                }

                redirect('/admin/reglists?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = User::query()->where('level', User::PENDED)->count();
        $page = paginate(setting('reglist'), $total);

        $users = User::query()
            ->where('level', User::PENDED)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('admin/reglists/index', compact('users', 'page'));
    }
}
