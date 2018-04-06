<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Chat;
use App\Models\User;

class ChatController extends AdminController
{
    /**
     * Главная страница
     */
    public function index()
    {
        if (getUser('newchat') !== statsNewChat()) {
            getUser()->update([
                'newchat' => statsNewChat()
            ]);
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

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
                redirect ('/admin/chat');
            } else {
                setInput(Request::all());
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

        return view('admin/chat/index', compact('posts', 'page'));
    }

    /**
     * Редактирование сообщения
     */
    public function edit($id)
    {
        $page  = int(Request::input('page', 1));

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

        if (Request::isMethod('post')) {

            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

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
                redirect('/admin/chat?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/chat/edit', compact('post', 'page'));
    }

    /**
     * Очистка чата
     */
    public function clear()
    {
        $token = check(Request::input('token'));

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

        redirect('/admin/chat');
    }
}
