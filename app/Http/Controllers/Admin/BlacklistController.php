<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BlacklistRequest;
use App\Http\Requests\Admin\DeleteSelectedRequest;
use App\Models\BlackList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlacklistController extends AdminController
{
    private string $type;

    /**
     * Конструктор
     */
    public function __construct(Request $request)
    {
        $types = ['email', 'login', 'domain'];

        $type = $request->input('type', 'email');

        if (! is_string($type) || ! in_array($type, $types, true)) {
            abort(404, __('admin.blacklists.type_not_found'));
        }

        $this->type = $type;
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $type = $this->type;

        $lists = BlackList::query()
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blacklist'))
            ->appends(['type' => $type]);

        return view('admin/blacklists/index', compact('lists', 'type'));
    }

    /**
     * Добавление записи
     */
    public function store(BlacklistRequest $request): RedirectResponse
    {
        BlackList::query()->create([
            'type'    => $this->type,
            'value'   => $request->validated('value'),
            'user_id' => auth()->id(),
        ]);

        return redirect('admin/blacklists?type=' . $this->type)
            ->with('success', __('main.record_added_success'));
    }

    /**
     * Удаление записей
     */
    public function delete(DeleteSelectedRequest $request): RedirectResponse
    {
        $page = $request->integer('page', 1);

        BlackList::query()
            ->where('type', $this->type)
            ->whereIn('id', $request->ids())
            ->delete();

        return redirect('admin/blacklists?type=' . $this->type . '&page=' . $page)
            ->with('success', __('main.records_deleted_success'));
    }
}
