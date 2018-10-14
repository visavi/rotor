<?php

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
     * @return string
     */
    public function index(): string
    {
        if (getUser('newchat') !== statsNewChat()) {
            getUser()->update([
                'newchat' => statsNewChat()
            ]);
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 5, 1500, ['msg' => 'Слишком длинное или короткое сообщение!']);

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

                setFlash('success', 'Сообщение успешно добавлено!');
                redirect ('/admin/chats');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Chat::query()->count();
        $page = paginate(setting('chatpost'), $total);

        $posts = Chat::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user', 'editUser')
            ->get();

        return view('admin/chats/index', compact('posts', 'page'));
    }

    /**
     * Редактирование сообщения
     *
     * @param int $id
     * @return string
     */
    public function edit(int $id): string
    {
        $page  = int($request->input('page', 1));

        if (! getUser()) {
            abort(403);
        }

        $post = Chat::query()->where('user_id', getUser('id'))->find($id);

        if (! $post) {
            abort('default', 'Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }

        if ($post->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if ($request->isMethod('post')) {

            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 5, 1500, ['msg' => 'Слишком длинное или короткое сообщение!']);

            if ($validator->isValid()) {

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Сообщение успешно отредактировано!');
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
     * @return void
     */
    public function clear(): void
    {
        $token = check($request->input('token'));

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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
