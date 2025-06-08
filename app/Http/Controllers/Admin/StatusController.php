<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $statuses = Status::query()->orderByDesc('topoint')->get();

        return view('admin/status/index', compact('statuses'));
    }

    /**
     * Добавление статуса
     */
    public function create(Request $request, Validator $validator): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $topoint = int($request->input('topoint'));
            $point = int($request->input('point'));
            $name = $request->input('name');
            $color = $request->input('color');

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($name, 3, 30, ['name' => __('statuses.status_length')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

            if ($validator->isValid()) {
                Status::query()->create([
                    'topoint' => $topoint,
                    'point'   => $point,
                    'name'    => $name,
                    'color'   => $color,
                ]);

                setFlash('success', __('statuses.status_success_added'));

                return redirect('admin/status');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/status/create');
    }

    /**
     * Редактирование статуса
     */
    public function edit(Request $request, Validator $validator): View|RedirectResponse
    {
        $id = int($request->input('id'));

        $status = Status::query()->find($id);

        if (! $status) {
            abort(404, __('statuses.status_not_found'));
        }

        if ($request->isMethod('post')) {
            $topoint = int($request->input('topoint'));
            $point = int($request->input('point'));
            $name = $request->input('name');
            $color = $request->input('color');

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($name, 3, 30, ['name' => __('statuses.status_length')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

            if ($validator->isValid()) {
                $status->update([
                    'topoint' => $topoint,
                    'point'   => $point,
                    'name'    => $name,
                    'color'   => $color,
                ]);

                setFlash('success', __('statuses.status_success_edited'));

                return redirect('admin/status');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/status/edit', compact('status'));
    }

    /**
     * Удаление статуса
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $id = int($request->input('id'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        $status = Status::query()->find($id);
        $validator->notEmpty($status, __('statuses.status_not_found'));

        if ($validator->isValid()) {
            $status->delete();

            setFlash('success', __('statuses.status_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/status');
    }
}
