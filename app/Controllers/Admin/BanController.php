<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Banhist;
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
     * Бан пользователя
     */
    public function edit()
    {
        $login = check(Request::input('user'));

        $user = User::query()->where('login', $login)->with('lastBan')->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        if (in_array($user->level, User::ADMIN_GROUPS)) {
            abort('default', 'Запрещено банить администрацию сайта!');
        }

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $time   = int(Request::input('time'));
            $type   = check(Request::input('type'));
            $reason = check(Request::input('reason'));
            $notice = check(Request::input('notice'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->false($user->level == User::BANNED && $user->timeban > SITETIME, 'Данный аккаунт уже заблокирован!')
                ->gt($time, 0, ['time' => 'Вы не указали время бана!'])
                ->in($type, ['minutes', 'hours', 'days'], ['type', 'Не выбрано время бана!'])
                ->length($reason, 5, 1000, ['reason' => 'Слишком длинная или короткая причина бана!'])
                ->length($notice, 0, 1000, ['notice' => 'Слишком большая заметка, не более 1000 символов!']);

            if ($validator->isValid()) {

                if ($type === 'days') {
                    $time = $time * 3600 * 24;
                } elseif ($type === 'hours') {
                    $time = $time * 3600;
                } else {
                    $time = $time * 60;
                }

                $user->update([
                    'level'    => User::BANNED,
                    'timeban'  => SITETIME + $time,
                ]);

                Banhist::query()->create([
                    'user_id'      => $user->id,
                    'send_user_id' => getUser('id'),
                    'type'         => Banhist::BAN,
                    'reason'       => $reason,
                    'term'         => $time,
                    'created_at'   => SITETIME,
                ]);

                $user->note()->updateOrCreate([], [
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Пользователь успешно заблокирован!');
                redirect('/admin/ban/edit?user=' . $user->login);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/ban/edit', compact('user'));
    }

    /**
     * Изменение бана
     */
    public function change()
    {
        $login = check(Request::input('user'));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        if (in_array($user->level, User::ADMIN_GROUPS)) {
            abort('default', 'Запрещено банить администрацию сайта!');
        }

        return view('admin/ban/change', compact('user'));
    }
}
