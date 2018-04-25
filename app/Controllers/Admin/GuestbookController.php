<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Guestbook;
use App\Models\User;

class GuestbookController extends AdminController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Guestbook::query()->count();
        $page = paginate(setting('bookpost'), $total);

        $posts = Guestbook::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user', 'editUser')
            ->get();

        return view('admin/guestbooks/index', compact('posts', 'page'));
    }

    /**
     * Редактирование сообщения
     */
    public function edit($id)
    {
        $page = int(Request::input('page'));
        $post = Guestbook::with('user')->find($id);

        if (! $post) {
            abort(404, 'Сообщения для редактирования не существует!');
        }

        if (Request::isMethod('post')) {

            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 5, setting('guesttextlength'), ['msg' => 'Слишком длинное или короткое сообщение!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/admin/guestbooks?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guestbooks/edit', compact('post', 'page'));
    }

    /**
     * Ответ на сообщение
     */
    public function reply($id)
    {
        $page = int(Request::input('page'));
        $post = Guestbook::with('user')->find($id);

        if (! $post) {
            abort(404, 'Сообщения для ответа не существует!');
        }

        if (Request::isMethod('post')) {

            $reply = check(Request::input('reply'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($reply, 5, setting('guesttextlength'), ['msg' => 'Слишком длинный или короткий ответ!']);

            if ($validator->isValid()) {

                $reply = antimat($reply);

                $post->update([
                    'reply' => $reply,
                ]);

                setFlash('success', 'Ответ успешно добавлен!');
                redirect('/admin/guestbooks?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guestbooks/reply', compact('post', 'page'));
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
            Guestbook::query()->whereIn('id', $del)->delete();

            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/guestbooks?page=' . $page);
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

            Guestbook::query()->truncate();

            setFlash('success', 'Гостевая книга успешно очищена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/guestbooks');
    }
}
