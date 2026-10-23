<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\NoticeRequest;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $notices = Notice::query()
            ->orderBy('id')
            ->with('user')
            ->get();

        return view('admin/notices/index', compact('notices'));
    }

    /**
     * Форма создания шаблона
     */
    public function create(): View
    {
        return view('admin/notices/create');
    }

    /**
     * Создание шаблона
     */
    public function store(NoticeRequest $request): RedirectResponse
    {
        $notice = Notice::query()->create([
            'type'    => $request->validated('type'),
            'name'    => $request->validated('name'),
            'text'    => $request->validated('text'),
            'user_id' => auth()->id(),
            'protect' => (int) $request->boolean('protect'),
        ]);

        return redirect('admin/notices/edit/' . $notice->id)
            ->with('success', __('admin.notices.notice_success_saved'));
    }

    /**
     * Форма редактирования шаблона
     */
    public function edit(int $id): View
    {
        $notice = $this->findNotice($id);

        return view('admin/notices/edit', compact('notice'));
    }

    /**
     * Редактирование шаблона
     */
    public function update(int $id, NoticeRequest $request): RedirectResponse
    {
        $notice = $this->findNotice($id);

        $notice->update([
            'name'    => $request->validated('name'),
            'text'    => $request->validated('text'),
            'user_id' => auth()->id(),
            'protect' => (int) $request->boolean('protect'),
        ]);

        return redirect('admin/notices/edit/' . $notice->id)
            ->with('success', __('admin.notices.notice_success_saved'));
    }

    /**
     * Удаление шаблона
     */
    public function delete(int $id): RedirectResponse
    {
        $notice = Notice::query()->find($id);

        if (! $notice) {
            return redirect('admin/notices')
                ->withErrors(__('admin.notices.notice_not_found'));
        }

        if ($notice->protect) {
            return redirect('admin/notices')
                ->withErrors(__('admin.notices.notice_protect'));
        }

        $notice->delete();

        return redirect('admin/notices')
            ->with('success', __('admin.notices.notice_success_deleted'));
    }

    /**
     * Возвращает шаблон или 404
     */
    private function findNotice(int $id): Notice
    {
        $notice = Notice::query()->find($id);

        if (! $notice) {
            abort(404, __('admin.notices.notice_not_found'));
        }

        return $notice;
    }
}
