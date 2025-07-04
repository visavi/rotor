<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Guestbook;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestbookController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $posts = Guestbook::query()
            ->orderByDesc('created_at')
            ->with('user', 'editUser', 'files')
            ->paginate(setting('bookpost'));

        $unpublished = Guestbook::query()->active(false)->count();

        return view('admin/guestbook/index', compact('posts', 'unpublished'));
    }

    /**
     * Редактирование сообщения
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page'));
        $post = Guestbook::with('user')->find($id);

        if (! $post) {
            abort(404, __('main.message_not_found'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
                ->length($msg, setting('guestbook_text_min'), setting('guestbook_text_max'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('main.message_edited_success'));

                return redirect()->route('admin.guestbook.index', ['page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/guestbook/edit', compact('post', 'page'));
    }

    /**
     * Ответ на сообщение
     */
    public function reply(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page'));
        $post = Guestbook::with('user')->find($id);

        if (! $post) {
            abort(404, __('main.message_not_found'));
        }

        if ($request->isMethod('post')) {
            $reply = $request->input('reply');

            $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
                ->length($reply, setting('guestbook_text_min'), setting('guestbook_text_max'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $post->update([
                    'reply' => $reply,
                ]);

                setFlash('success', __('guestbook.answer_success_added'));

                return redirect()->route('admin.guestbook.index', ['page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/guestbook/reply', compact('post', 'page'));
    }

    /**
     * Удаление сообщений
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $del = intar($request->input('chosen'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            $posts = Guestbook::query()->whereIn('id', $del)->get();

            $posts->each(static function (Guestbook $post) {
                $post->delete();
            });

            clearCache('statGuestbook');
            setFlash('success', __('main.messages_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.guestbook.index', ['page' => $page]);
    }

    /**
     * Активация сообщений
     */
    public function publish(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $active = intar($request->input('chosen'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($active, __('validator.published'));

        if ($validator->isValid()) {
            $posts = Guestbook::query()->whereIn('id', $active)->get();

            $posts->each(static function (Guestbook $post) {
                $post->update([
                    'active' => true,
                ]);
            });

            clearCache('statGuestbook');
            setFlash('success', __('main.messages_published_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.guestbook.index', ['page' => $page]);
    }

    /**
     * Очистка сообщений
     */
    public function clear(Request $request, Validator $validator): RedirectResponse
    {
        $validator
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true(isAdmin(User::BOSS), __('main.page_only_owner'));

        if ($validator->isValid()) {
            Guestbook::query()->truncate();
            clearCache('statGuestbook');

            setFlash('success', __('guestbook.messages_success_cleared'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.guestbook.index');
    }
}
