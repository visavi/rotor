<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DeleteSelectedRequest;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BanhistController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $records = Banhist::query()
            ->orderByDesc('created_at')
            ->with('user', 'sendUser')
            ->paginate(setting('listbanhist'));

        return view('admin/banhists/index', compact('records'));
    }

    /**
     * История банов
     */
    public function view(Request $request): View
    {
        $user = User::findByLoginOrFail($request->input('user'));

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user', 'sendUser')
            ->paginate(setting('listbanhist'))
            ->appends(['user' => $user->login]);

        return view('admin/banhists/view', compact('user', 'banhist'));
    }

    /**
     * Удаление банов
     */
    public function delete(DeleteSelectedRequest $request): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $login = $request->string('user')->value();

        Banhist::query()->whereIn('id', $request->ids())->delete();

        $redirect = $login
            ? 'admin/banhists/view?' . http_build_query(['user' => $login, 'page' => $page])
            : 'admin/banhists?page=' . $page;

        return redirect($redirect)
            ->with('success', __('main.records_deleted_success'));
    }
}
