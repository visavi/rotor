<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;

class StatusController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $statuses = Status::query()->orderBy('topoint', 'desc')->get();

        return view('admin/status/index', compact('statuses'));
    }

    /**
     * Добавление статуса
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $topoint = int($request->input('topoint'));
            $point   = int($request->input('point'));
            $name    = check($request->input('name'));
            $color   = check($request->input('color'));

            $validator
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($name, 3, 30, ['name' => 'Слишком длинное или короткое название статуса!'])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

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
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/status/create');
    }

    /**
     * Редактирование статуса
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(Request $request, Validator $validator): string
    {
        $id = int($request->input('id'));

        $status = Status::query()->find($id);

        if (! $status) {
            abort(404, 'Выбранный вами статус не найден!');
        }

        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $topoint = int($request->input('topoint'));
            $point   = int($request->input('point'));
            $name    = check($request->input('name'));
            $color   = check($request->input('color'));

            $validator
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($name, 3, 30, ['name' => 'Слишком длинное или короткое название статуса!'])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

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
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/status/edit', compact('status'));
    }

    /**
     * Удаление статуса
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));
        $id    = int($request->input('id'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'));

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
