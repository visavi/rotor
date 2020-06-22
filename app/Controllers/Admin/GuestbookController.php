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
        $posts = Guestbook::query()
            ->orderByDesc('created_at')
            ->with('user', 'editUser')
            ->paginate(setting('bookpost'));

        return view('admin/guestbook/index', compact('posts'));
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
            abort(404, __('main.message_not_found'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->equal($request->input('token'), $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 5, setting('guesttextlength'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('main.message_edited_success'));
                redirect('/admin/guestbook?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guestbook/edit', compact('post', 'page'));
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
            abort(404, __('main.message_not_found'));
        }

        if ($request->isMethod('post')) {
            $reply = $request->input('reply');

            $validator->equal($request->input('token'), $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($reply, 5, setting('guesttextlength'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $post->update([
                    'reply' => $reply,
                ]);

                setFlash('success', __('guestbook.answer_success_added'));
                redirect('/admin/guestbook?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/guestbook/reply', compact('post', 'page'));
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
        $page = int($request->input('page', 1));
        $del  = intar($request->input('del'));

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Guestbook::query()->whereIn('id', $del)->delete();

            clearCache('statGuestbook');
            setFlash('success', __('main.messages_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/guestbook?page=' . $page);
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
        $validator
            ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->true(isAdmin(User::BOSS), __('main.page_only_owner'));

        if ($validator->isValid()) {
            Guestbook::query()->truncate();
            clearCache('statGuestbook');

            setFlash('success', __('guestbook.messages_success_cleared'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/guestbook');
    }
}
