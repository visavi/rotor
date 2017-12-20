<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Status;
use App\Models\User;

class StatusController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $statuses = Status::query()->orderBy('topoint', 'desc')->get();

        return view('admin/status/index', compact('statuses'));
    }

    /**
     * Добавление статуса
     */
    public function create()
    {
        if (Request::isMethod('post')) {
            $token   = check(Request::input('token'));
            $topoint = abs(intval(Request::input('topoint')));
            $point   = abs(intval(Request::input('point')));
            $name    = check(Request::input('name'));
            $color   = check(Request::input('color'));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($name, 5, 30, ['name' => 'Слишком длинное или короткое название статуса!'])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => 'Недопустимый формат цвета статуса! (пример #ff0000)'], false);

            if ($validator->isValid()) {

                Status::query()->create([
                    'topoint' => $topoint,
                    'point'   => $point,
                    'name'    => $name,
                    'color'   => $color,
                ]);

                setFlash('success', 'Статус успешно добавлен!');
                redirect('/admin/status');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/status/create');
    }

    /**
     * Редактирование статуса
     */
    public function edit()
    {
        $id = abs(intval(Request::input('id')));

        $status = Status::query()->find($id);

        if (! $status) {
            abort(404, 'Выбранный вами статус не найден!');
        }

        if (Request::isMethod('post')) {
            $token   = check(Request::input('token'));
            $topoint = abs(intval(Request::input('topoint')));
            $point   = abs(intval(Request::input('point')));
            $name    = check(Request::input('name'));
            $color   = check(Request::input('color'));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($name, 5, 30, ['name' => 'Слишком длинное или короткое название статуса!'])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => 'Недопустимый формат цвета статуса! (пример #ff0000)'], false);

            if ($validator->isValid()) {

                $status->update([
                    'topoint' => $topoint,
                    'point'   => $point,
                    'name'    => $name,
                    'color'   => $color,
                ]);

                setFlash('success', 'Статус успешно изменен!');
                redirect('/admin/status');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/status/edit', compact('status'));
    }

    /**
     * Удаление статуса
     */
    public function delete()
    {
        $token = check(Request::input('token'));
        $id    = abs(intval(Request::input('id')));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        $status = Status::query()->find($id);
        $validator->notEmpty($status, 'Выбранный для удаления статус не найден!');

        if ($validator->isValid()) {

            $status->delete();

            setFlash('success', 'Статус успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/status');
    }
}
