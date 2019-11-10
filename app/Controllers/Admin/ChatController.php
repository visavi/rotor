<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends AdminController
{
    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        if (getUser('newchat') !== statsNewChat()) {
            getUser()->update([
                'newchat' => statsNewChat()
            ]);
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 5, 1500, ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $post = Chat::query()->orderBy('created_at')->first();

                if (
                    $post &&
                    $post->created_at + 1800 > SITETIME &&
                    getUser('id') === $post->user_id &&
                    (utfStrlen($msg) + utfStrlen($post->text) <= 1500)
                ) {

                    $newpost = $post->text . "\n\n" . '[i][size=1]Добавлено через ' . makeTime(SITETIME - $post->created_at) . ' сек.[/size][/i]' . "\n" . $msg;

                    $post->update([
                        'text' => $newpost,
                    ]);

                } else {
                    Chat::query()->create([
                        'user_id'    => getUser('id'),
                        'text'       => $msg,
                        'ip'         => getIp(),
                        'brow'       => getBrowser(),
                        'created_at' => SITETIME,
                    ]);
                }

                setFlash('success', __('main.message_added_success'));
                redirect ('/admin/chats');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $posts = Chat::query()
            ->orderByDesc('created_at')
            ->with('user', 'editUser')
            ->paginate(setting('chatpost'));

        return view('admin/chats/index', compact('posts'));
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
        $page  = int($request->input('page', 1));

        if (! getUser()) {
            abort(403);
        }

        /** @var Chat $post */
        $post = Chat::query()->where('user_id', getUser('id'))->find($id);

        if (! $post) {
            abort('default', 'Сообщение удалено или вы не автор этого сообщения!');
        }

        if ($post->created_at + 600 < SITETIME) {
            abort('default', __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {

            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 5, 1500, ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('main.message_edited_success'));
                redirect('/admin/chats?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/chats/edit', compact('post', 'page'));
    }

    /**
     * Очистка чата
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
            ->true(isAdmin(User::BOSS), 'Очищать чат может только владелец!');

        if ($validator->isValid()) {
             Chat::query()->truncate();

            setFlash('success', 'Админ-чат успешно очищен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/chats');
    }
}
