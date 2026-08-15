<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Status;
use App\Support\Validator;
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
                ->length($name, 3, 30, ['name' => __('statuses.status_length')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

            if ($validator->isValid()) {
                Status::query()->create([
                    'topoint' => $topoint,
                    'point'   => $point,
                    'name'    => $name,
                    'color'   => $color,
                ]);

                return redirect('admin/status')
                    ->with('success', __('statuses.status_success_added'));
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($validator->getErrors());
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
                ->length($name, 3, 30, ['name' => __('statuses.status_length')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

            if ($validator->isValid()) {
                $status->update([
                    'topoint' => $topoint,
                    'point'   => $point,
                    'name'    => $name,
                    'color'   => $color,
                ]);

                return redirect('admin/status')
                    ->with('success', __('statuses.status_success_edited'));
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        return view('admin/status/edit', compact('status'));
    }

    /**
     * Удаление статуса
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $id = int($request->input('id'));

        $status = Status::query()->find($id);
        $validator->notEmpty($status, __('statuses.status_not_found'));

        if (! $validator->isValid()) {
            return redirect('admin/status')
                ->withErrors($validator->getErrors());
        }

        $status->delete();

        return redirect('admin/status')
            ->with('success', __('statuses.status_success_deleted'));
    }
}
