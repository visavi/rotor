<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StatusRequest;
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
     * Форма добавления статуса
     */
    public function create(): View
    {
        return view('admin/status/create');
    }

    /**
     * Добавление статуса
     */
    public function store(StatusRequest $request): RedirectResponse
    {
        Status::query()->create($this->fields($request));

        return redirect('admin/status')
            ->with('success', __('statuses.status_success_added'));
    }

    /**
     * Форма редактирования статуса
     */
    public function edit(Request $request): View
    {
        $status = $this->findStatus($request);

        return view('admin/status/edit', compact('status'));
    }

    /**
     * Редактирование статуса
     */
    public function update(StatusRequest $request): RedirectResponse
    {
        $this->findStatus($request)->update($this->fields($request));

        return redirect('admin/status')
            ->with('success', __('statuses.status_success_edited'));
    }

    /**
     * Удаление статуса
     */
    public function delete(Request $request): RedirectResponse
    {
        $id = int($request->input('id'));

        $status = Status::query()->find($id);

        if (! $status) {
            return redirect('admin/status')
                ->withErrors(__('statuses.status_not_found'));
        }

        $status->delete();

        return redirect('admin/status')
            ->with('success', __('statuses.status_success_deleted'));
    }

    /**
     * Возвращает статус из запроса или 404
     */
    private function findStatus(Request $request): Status
    {
        $status = Status::query()->find(int($request->input('id')));

        if (! $status) {
            abort(404, __('statuses.status_not_found'));
        }

        return $status;
    }

    /**
     * Возвращает поля статуса из запроса
     */
    private function fields(StatusRequest $request): array
    {
        return [
            'topoint' => int($request->input('topoint')),
            'point'   => int($request->input('point')),
            'name'    => $request->validated('name'),
            'color'   => $request->validated('color'),
        ];
    }
}
