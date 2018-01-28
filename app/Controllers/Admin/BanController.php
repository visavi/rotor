<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Note;
use App\Models\User;

class BanController extends AdminController
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
     */
    public function index()
    {
        return view('admin/ban/index');
    }

    /**
     * Редактирование пользователя
     */
    public function edit()
    {
        $login = check(Request::input('user'));

        $user = User::query()->where('login', $login)->with('lastBan')->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }
/*
        if (in_array($user->level, User::ADMIN_GROUPS)) {
            abort('default', 'Запрещено банить администрацию сайта!');
        }*/

        $note = Note::query()->where('user_id', $user->id)->first();

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $time   = int(Request::input('time'));
            $type   = check(Request::input('type'));
            $reason = check(Request::input('reason'));
            $notice = check(Request::input('notice'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->false($user->level == User::BANNED && $user->timeban > SITETIME, 'Данный аккаунт уже заблокирован!');

            if ($validator->isValid()) {

/*                $record = [
                    'user_id'      => $user->id,
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ];

                Note::saveNote($note, $record);*/

                setFlash('success', 'Пользователь успешно заблокирован!');
                redirect('/admin/ban/edit?user=' . $user->login);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }


        return view('admin/ban/edit', compact('user', 'note'));
    }
}
