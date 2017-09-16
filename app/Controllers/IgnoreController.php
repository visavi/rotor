<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Ignore;
use App\Models\User;

class IgnoreController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isUser()) {
            abort(403, 'Для просмотра игнор-листа необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {
            $page = abs(intval(Request::input('page', 1)));
            $token = check(Request::input('token'));
            $login = check(Request::input('user'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

            $user = User::query()->where('login', $login)->first();
            $validation->addRule('not_empty', $user, 'Данного пользователя не существует!');

            if ($user) {
                $validation->addRule('not_equal', [$user->login, getUsername()], 'Запрещено добавлять свой логин!');

                $totalIgnore = Ignore::query()->where('user_id', getUserId())->count();
                $validation->addRule('min', [$totalIgnore, setting('limitignore')], 'Ошибка! Игнор-лист переполнен (Максимум ' . setting('limitignore') . ' пользователей!)');

                $validation->addRule('custom', ! isIgnore(user(), $user), 'Данный пользователь уже есть в игнор-листе!');

                $validation->addRule('custom', ! in_array($user->level, [101, 102, 103, 105]), 'Запрещено добавлять в игнор администрацию сайта');
            }

            if ($validation->run()) {

                Ignore::query()->create([
                    'user_id'    => getUserId(),
                    'ignore_id'  => $user->id,
                    'created_at' => SITETIME,
                ]);

                if (! isIgnore($user, user())) {
                    $message = 'Пользователь [b]' . getUsername() . '[/b] добавил вас в свой игнор-лист!';
                    sendPrivate($user->id, getUserId(), $message);
                }

                setFlash('success', 'Пользователь успешно добавлен в игнор-лист!');
                redirect('/ignore?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $total = Ignore::query()->where('user_id', getUserId())->count();
        $page = paginate(setting('ignorlist'), $total);

        $ignores = Ignore::query()
            ->where('user_id', getUserId())
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('ignoring')
            ->get();

        return view('ignore/index', compact('ignores', 'page'));
    }

    /**
     * Заметка для пользователя
     */
    public function note($id)
    {
        $ignore = Ignore::query()
            ->where('user_id', getUserId())
            ->where('id', $id)
            ->first();

        if (! $ignore) {
            abort('default', 'Запись не найдена');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->addRule('string', $msg, ['msg' => 'Слишком большая заметка, не более 1000 символов!'], true, 0, 1000);

            if ($validation->run()) {

                $ignore->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Заметка успешно отредактирована!');
                redirect("/ignore");
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('ignore/note', compact('ignore'));
    }

    /**
     * Удаление контактов
     */
    public function delete()
    {
        $page  = abs(intval(Request::input('page', 1)));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', $del, 'Ошибка удаления! Отсутствуют выбранные пользователи');

        if ($validation->run()) {

            Ignore::query()
                ->where('user_id', getUserId())
                ->whereIn('id', $del)
                ->delete();

            setFlash('success', 'Выбранные пользователи успешно удалены!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect("/ignore?page=$page");
    }
}
