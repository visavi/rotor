<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Notice;
use App\Models\User;

class NoticeController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $notices = Notice::query()
            ->orderBy('id')
            ->with('user')
            ->get();

        return view('admin/notice/index', compact('notices'));
    }

    /**
     * Создание шаблона
     */
    public function create()
    {
        if (Request::isMethod('post')) {
            $token   = check(Request::input('token'));
            $type    = check(Request::input('type'));
            $name    = check(Request::input('name'));
            $text    = check(Request::input('text'));
            $protect = Request::has('protect') ? 1 : 0;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->regex($type, '|^[a-z0-9_\-]+$|i', ['type' => 'Недопустимое название типа шаблона!'])
                ->length($type, 3, 20, ['type' => 'Слишком длинный или короткий тип шаблона!'])
                ->length($name, 5, 100, ['name' => 'Слишком длинное или короткое название шаблона!'])
                ->length($text, 10, 65000, ['text' => 'Слишком длинный или короткий текст шаблона!']);

            $duplicate = Notice::query()->where('type', $type)->first();
            $validator->empty($duplicate, ['type' => 'Данный тип уже имеетеся в списке!']);

            if ($validator->isValid()) {

                $notice = Notice::query()->create([
                    'type'       => $type,
                    'name'       => $name,
                    'text'       => $text,
                    'user_id'    => getUser('id'),
                    'protect'    => $protect,
                    'created_at' => SITETIME,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', 'Шаблон успешно сохранен!');
                redirect('/admin/notice/edit/' . $notice->id);

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/notice/create');
    }

    /**
     * Редактирование шаблона
     */
    public function edit($id)
    {
        $notice = Notice::query()->find($id);

        if (! $notice) {
            abort(404, 'Данного шаблона не существует!');
        }

        if (Request::isMethod('post')) {
            $token   = check(Request::input('token'));
            $type    = check(Request::input('type'));
            $name    = check(Request::input('name'));
            $text    = check(Request::input('text'));
            $protect = Request::has('protect') ? 1 : 0;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->regex($type, '|^[a-z0-9_\-]+$|i', ['type' => 'Недопустимое название типа шаблона!'])
                ->length($type, 3, 20, ['type' => 'Слишком длинный или короткий тип шаблона!'])
                ->length($name, 5, 100, ['name' => 'Слишком длинное или короткое название шаблона!'])
                ->length($text, 10, 65000, ['text' => 'Слишком длинный или короткий текст шаблона!']);

            $duplicate = Notice::query()->where('id', '<>', $notice->id)->where('type', $type)->first();
            $validator->empty($duplicate, ['type' => 'Данный тип уже имеетеся в списке!']);

            if ($validator->isValid()) {

                $notice->update([
                    'type'       => $type,
                    'name'       => $name,
                    'text'       => $text,
                    'user_id'    => getUser('id'),
                    'protect'    => $protect,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', 'Шаблон успешно сохранен!');
                redirect('/admin/notice/edit/' . $notice->id);

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/notice/edit', compact('notice'));
    }

    /**
     * Удаление шаблона
     */
    public function delete($id)
    {
        $token = check(Request::input('token'));

        $notice = Notice::query()->find($id);

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($notice, 'Не найден шаблон для удаления!')
            ->empty($notice->protect, 'Запрещено удалять защищенный шаблон!');

        if ($validator->isValid()) {

            $notice->delete();

            setFlash('success', 'Выбранный шаблон успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/notice');
    }
}
