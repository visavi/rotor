<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ReglistRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReglistController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $users = User::query()
            ->where('level', User::PENDED)
            ->orderByDesc('created_at')
            ->paginate(setting('reglist'));

        return view('admin/reglists/index', compact('users'));
    }

    /**
     * Одобрение или удаление отмеченных регистраций
     */
    public function update(ReglistRequest $request): RedirectResponse
    {
        $page = int($request->input('page', 1));

        // Только ожидающие: иначе подделанный id одобрил бы или удалил любого
        $users = User::query()
            ->where('level', User::PENDED)
            ->whereIn('id', $request->ids());

        if ($request->validated('action') === 'yes') {
            // Ссылка из письма больше не нужна: одобренный аккаунт уже активен
            $users->update(['level' => User::USER, 'confirm_token' => null]);

            $message = __('admin.reglists.users_success_approved');
        } else {
            $users->get()->each(static function (User $user) {
                $user->delete();
            });

            $message = __('admin.reglists.users_success_deleted');
        }

        return redirect('admin/reglists?page=' . $page)
            ->with('success', $message);
    }
}
