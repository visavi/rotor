<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Forum;
use App\Models\User;

class ForumController extends AdminController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('lastTopic.lastPost.user')
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort('default', 'Разделы форума еще не созданы!');
        }

        return view('admin/forum/index', compact('forums'));
    }

    /**
     * Редактирование форума
     */
    public function edit($id)
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $forum = Forum::query()->with('children')->find($id);

        if (! $forum) {
            abort(404, 'Данного раздела не существует!');
        }

        $forums = Forum::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $parent = int(Request::input('parent'));
            $title  = check(Request::input('title'));
            $desc   = check(Request::input('desc'));
            $sort   = check(Request::input('sort'));
            $closed = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название раздела!'])
                ->length($desc, 0, 100, ['desc' => 'Слишком длинное описания раздела!'])
                ->notEqual($parent, $forum->id, ['parent' => 'Недопустимый выбор родительского раздела!']);


            if (! empty($parent) && $forum->children->isNotEmpty()) {
                $validator->addError(['parent' => 'Текущий раздел имеет подфорумы!']);
            }

            if ($validator->isValid()) {

                $forum->update([
                    'parent_id' => $parent,
                    'title'     => $title,
                    'desc'      => $desc,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', 'Раздел успешно отредактирован!');
                redirect('/admin/forum');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/forum/edit', compact('forums', 'forum'));
    }

    /**
     * Пересчет сообщений
     */
    public function restatement()
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            restatement('forum');

            setFlash('success', 'Сообщения успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/forum');
    }
}
