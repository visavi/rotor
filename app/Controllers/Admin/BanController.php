<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Http\Request;

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
     *
     * @return string
     */
    public function index(): string
    {
        return view('admin/bans/index');
    }

    /**
     * Бан пользователя
     *
     * @return string
     */
    public function edit(): string
    {
        $login = check($request->input('user'));

        $user = User::query()->where('login', $login)->with('lastBan')->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        if (\in_array($user->level, User::ADMIN_GROUPS, true)) {
            abort('default', 'Запрещено банить администрацию сайта!');
        }

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $time   = int($request->input('time'));
            $type   = check($request->input('type'));
            $reason = check($request->input('reason'));
            $notice = check($request->input('notice'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->false($user->level === User::BANNED && $user->timeban > SITETIME, 'Данный аккаунт уже заблокирован!')
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
                    'level'   => User::BANNED,
                    'timeban' => SITETIME + $time,
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
                redirect('/admin/bans/edit?user=' . $user->login);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/bans/edit', compact('user'));
    }

    /**
     * Изменение бана
     *
     * @return string
     */
    public function change(): string
    {
        $login = check($request->input('user'));

        $user = User::query()->where('login', $login)->with('lastBan')->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        if ($user->level !== User::BANNED || $user->timeban < SITETIME) {
            abort('default', 'Данный пользователь не забанен!');
        }

        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $timeban = check($request->input('timeban'));
            $reason  = check($request->input('reason'));

            $timeban = strtotime($timeban);
            $term    = $timeban - SITETIME;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->gt($term, 0, ['timeban' => 'Слишком маленькое время бана!'])
                ->length($reason, 5, 1000, ['reason' => 'Слишком длинная или короткая причина бана!']);

            if ($validator->isValid()) {

                $user->update([
                    'level'   => User::BANNED,
                    'timeban' => $timeban,
                ]);

                Banhist::query()->create([
                    'user_id'      => $user->id,
                    'send_user_id' => getUser('id'),
                    'type'         => Banhist::CHANGE,
                    'reason'       => $reason,
                    'term'         => $term,
                    'created_at'   => SITETIME,
                ]);

                setFlash('success', 'Данные успешно изменены!');
                redirect('/admin/bans/edit?user=' . $user->login);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/bans/change', compact('user'));
    }

    /**
     * Снятие бана
     *
     * @return void
     */
    public function unban(): void
    {
        $token = check($request->input('token'));
        $login = check($request->input('user'));

        $user = User::query()->where('login', $login)->with('lastBan')->first();

        if (! $user) {
            abort(404, 'Пользователь не найден!');
        }

        if ($user->level !== User::BANNED || $user->timeban < SITETIME) {
            abort('default', 'Данный пользователь не забанен!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {

            $user->update([
                'level'   => User::USER,
                'timeban' => null,
            ]);

            Banhist::query()->create([
                'user_id'      => $user->id,
                'send_user_id' => getUser('id'),
                'type'         => Banhist::UNBAN,
                'created_at'   => SITETIME,
            ]);

            setFlash('success', 'Пользователь успешно разбанен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/bans/edit?user=' . $user->login);
    }
}
