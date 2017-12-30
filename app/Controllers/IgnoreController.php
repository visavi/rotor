<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
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

        if (! getUser()) {
            abort(403, 'Для просмотра игнор-листа необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {
            $page  = int(Request::input('page', 1));
            $token = check(Request::input('token'));
            $login = check(Request::input('user'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

            $user = User::query()->where('login', $login)->first();
            $validator->notEmpty($user, 'Данного пользователя не существует!');

            if ($user) {
                $validator->notEqual($user->login, getUser('login'), 'Запрещено добавлять свой логин!');

                $totalIgnore = Ignore::query()->where('user_id', getUser('id'))->count();
                $validator->lte($totalIgnore, setting('limitignore'), 'Ошибка! Игнор-лист переполнен (Максимум ' . setting('limitignore') . ' пользователей!)');

                $validator->false(isIgnore(getUser(), $user), 'Данный пользователь уже есть в игнор-листе!');
                $validator->notIn($user->level, User::ADMIN_GROUPS, 'Запрещено добавлять в игнор администрацию сайта');
            }

            if ($validator->isValid()) {

                Ignore::query()->create([
                    'user_id'    => getUser('id'),
                    'ignore_id'  => $user->id,
                    'created_at' => SITETIME,
                ]);

                if (! isIgnore($user, getUser())) {
                    $message = 'Пользователь [b]' . getUser('login') . '[/b] добавил вас в свой игнор-лист!';
                    sendPrivate($user, getUser(), $message);
                }

                setFlash('success', 'Пользователь успешно добавлен в игнор-лист!');
                redirect('/ignore?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Ignore::query()->where('user_id', getUser('id'))->count();
        $page = paginate(setting('ignorlist'), $total);

        $ignores = Ignore::query()
            ->where('user_id', getUser('id'))
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
            ->where('user_id', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $ignore) {
            abort('default', 'Запись не найдена');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 0, 1000, ['msg' => 'Слишком большая заметка, не более 1000 символов!']);

            if ($validator->isValid()) {

                $ignore->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Заметка успешно отредактирована!');
                redirect('/ignore');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('ignore/note', compact('ignore'));
    }

    /**
     * Удаление контактов
     */
    public function delete()
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Ошибка удаления! Отсутствуют выбранные пользователи');

        if ($validator->isValid()) {

            Ignore::query()
                ->where('user_id', getUser('id'))
                ->whereIn('id', $del)
                ->delete();

            setFlash('success', 'Выбранные пользователи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/ignore?page=' . $page);
    }
}
