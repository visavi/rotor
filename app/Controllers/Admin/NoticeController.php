<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Http\Request;

class NoticeController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, trans('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $notices = Notice::query()
            ->orderBy('id')
            ->with('user')
            ->get();

        return view('admin/notices/index', compact('notices'));
    }

    /**
     * Создание шаблона
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $type    = check($request->input('type'));
            $name    = check($request->input('name'));
            $text    = check($request->input('text'));
            $protect = empty($request->input('protect')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->regex($type, '|^[a-z0-9_\-]+$|i', ['type' => 'Недопустимое название типа шаблона!'])
                ->length($type, 3, 20, ['type' => 'Слишком длинный или короткий тип шаблона!'])
                ->length($name, 5, 100, ['name' => trans('validator.title')])
                ->length($text, 10, 65000, ['text' => trans('validator.text')]);

            $duplicate = Notice::query()->where('type', $type)->first();
            $validator->empty($duplicate, ['type' => 'Данный тип уже имеетеся в списке!']);

            if ($validator->isValid()) {

                /** @var Notice $notice */
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
                redirect('/admin/notices/edit/' . $notice->id);

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/notices/create');
    }

    /**
     * Редактирование шаблона
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        /** @var Notice $notice */
        $notice = Notice::query()->find($id);

        if (! $notice) {
            abort(404, 'Данного шаблона не существует!');
        }

        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $name    = check($request->input('name'));
            $text    = check($request->input('text'));
            $protect = empty($request->input('protect')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($name, 5, 100, ['name' => trans('validator.title')])
                ->length($text, 10, 65000, ['text' => trans('validator.text')]);

            if ($validator->isValid()) {

                $notice->update([
                    'name'       => $name,
                    'text'       => $text,
                    'user_id'    => getUser('id'),
                    'protect'    => $protect,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', 'Шаблон успешно сохранен!');
                redirect('/admin/notices/edit/' . $notice->id);

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/notices/edit', compact('notice'));
    }

    /**
     * Удаление шаблона
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        /** @var Notice $notice */
        $notice = Notice::query()->find($id);

        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->notEmpty($notice, 'Не найден шаблон для удаления!')
            ->empty($notice->protect, 'Запрещено удалять защищенный шаблон!');

        if ($validator->isValid()) {

            $notice->delete();

            setFlash('success', 'Выбранный шаблон успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/notices');
    }
}
