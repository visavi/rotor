<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Guest;
use App\Models\User;

class GuestController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Guest::query()->count();
        $page = paginate(setting('bookpost'), $total);

        $posts = Guest::query()
            ->orderBy('created_at', 'desc')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->with('user', 'editUser')
            ->get();

        return view('admin/guest/index', compact('posts', 'page'));
    }

    /**
     * Редактирование сообщения
     */
    public function edit($id)
    {
        $page = int(Request::input('page'));
        $post = Guest::with('user')->find($id);

        if (! $post) {
            abort(404, 'Сообщения для редактирования не существует!');
        }

        if (Request::isMethod('post')) {

            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 5, setting('guesttextlength'), ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/admin/book?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guest/edit', compact('post', 'page'));
    }

    /**
     * Ответ на сообщение
     */
    public function reply($id)
    {
        $page = int(Request::input('page'));
        $post = Guest::with('user')->find($id);

        if (! $post) {
            abort(404, 'Сообщения для ответа не существует!');
        }

        if (Request::isMethod('post')) {

            $reply = check(Request::input('reply'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($reply, 5, setting('guesttextlength'), ['msg' => 'Ошибка! Слишком длинный или короткий ответ!']);

            if ($validator->isValid()) {

                $reply = antimat($reply);

                $post->update([
                    'reply' => $reply,
                ]);

                setFlash('success', 'Ответ успешно добавлен!');
                redirect('/admin/book?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guest/reply', compact('post', 'page'));
    }

    /**
     * Удаление сообщений
     */
    public function delete()
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные сообщения для удаления!');

        if ($validator->isValid()) {
            Guest::query()->whereIn('id', $del)->delete();

            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/book?page=' . $page);
    }

    /**
     * Очистка сообщений
     */
    public function clear()
    {
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(isAdmin(User::BOSS), 'Очищать гостевую может только владелец!');

        if ($validator->isValid()) {

            Guest::query()->truncate();

            setFlash('success', 'Гостевая книга успешно очищен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/book');
    }
}
