<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Guestbook;
use App\Models\User;
use Illuminate\Http\Request;

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
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        $page = int($request->input('page'));
        $post = Guestbook::with('user')->find($id);

        if (! $post) {
            abort(404, 'Сообщения для редактирования не существует!');
        }

        if ($request->isMethod('post')) {

            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 5, setting('guesttextlength'), ['msg' => __('validator.text')]);

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
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guestbooks/edit', compact('post', 'page'));
    }

    /**
     * Ответ на сообщение
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function reply(int $id, Request $request, Validator $validator): string
    {
        $page = int($request->input('page'));
        $post = Guestbook::with('user')->find($id);

        if (! $post) {
            abort(404, 'Сообщения для ответа не существует!');
        }

        if ($request->isMethod('post')) {

            $reply = check($request->input('reply'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($reply, 5, setting('guesttextlength'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {

                $reply = antimat($reply);

                $post->update([
                    'reply' => $reply,
                ]);

                setFlash('success', 'Ответ успешно добавлен!');
                redirect('/admin/guestbooks?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guestbooks/reply', compact('post', 'page'));
    }

    /**
     * Удаление сообщений
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Guestbook::query()->whereIn('id', $del)->delete();

            clearCache(['statguestbook']);
            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/guestbooks?page=' . $page);
    }

    /**
     * Очистка сообщений
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function clear(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        $validator
            ->equal($token, $_SESSION['token'], __('validator.token'))
            ->true(isAdmin(User::BOSS), 'Очищать гостевую может только владелец!');

        if ($validator->isValid()) {

            clearCache(['statguestbook']);
            Guestbook::query()->truncate();

            setFlash('success', 'Гостевая книга успешно очищена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/guestbooks');
    }
}
