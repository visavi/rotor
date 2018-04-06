<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\User;

class BanhistController extends AdminController
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
        $total = Banhist::query()->count();
        $page = paginate(setting('listbanhist'), $total);

        $records = Banhist::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user', 'sendUser')
            ->get();

        return view('admin/banhist/index', compact('records', 'page'));
    }

    /**
     * История банов
     */
    public function view()
    {
        $login = check(Request::input('user'));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Banhist::query()->where('user_id', $user->id)->count();
        $page = paginate(setting('listbanhist'), $total);

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user', 'sendUser')
            ->get();

        return view('admin/banhist/view', compact('user', 'banhist', 'page'));
    }

    /**
     * Удаление банов
     */
    public function delete()
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));
        $login = check(Request::input('user'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные записи для удаления!');

        if ($validator->isValid()) {
            Banhist::query()->whereIn('id', $del)->delete();

            setFlash('success', 'Выбранные записи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        if ($login) {
            redirect('/admin/banhist/view?user=' . $login . '&page=' . $page);
        } else {
            redirect('/admin/banhist?page=' . $page);
        }
    }
}
