<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BanRequest;
use App\Http\Requests\Admin\ChangeBanRequest;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class BanController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        return view('admin/bans/index');
    }

    /**
     * Форма бана пользователя
     */
    public function edit(Request $request): View
    {
        $user = $this->findBannableUser($request);

        return view('admin/bans/edit', compact('user'));
    }

    /**
     * Бан пользователя
     */
    public function update(BanRequest $request): RedirectResponse
    {
        $user = $this->findBannableUser($request);

        if ($user->level === User::BANNED && $user->timeban?->isFuture()) {
            return redirect($this->editUrl($user))
                ->withInput()
                ->withErrors(__('admin.bans.user_banned'));
        }

        $term = $request->term();

        $user->update([
            'level'   => User::BANNED,
            'timeban' => now()->addSeconds($term),
        ]);

        Banhist::query()->create([
            'user_id'      => $user->id,
            'send_user_id' => getUser('id'),
            'type'         => Banhist::BAN,
            'reason'       => $request->validated('reason'),
            'term'         => $term,
        ]);

        return redirect($this->editUrl($user))
            ->with('success', __('admin.bans.success_banned'));
    }

    /**
     * Форма изменения бана
     */
    public function change(Request $request): View
    {
        $user = $this->findBannedUser($request);

        return view('admin/bans/change', compact('user'));
    }

    /**
     * Изменение бана
     */
    public function updateChange(ChangeBanRequest $request): RedirectResponse
    {
        $user = $this->findBannedUser($request);

        $timeban = Date::parse($request->validated('timeban'));

        $user->update([
            'level'   => User::BANNED,
            'timeban' => $timeban,
        ]);

        Banhist::query()->create([
            'user_id'      => $user->id,
            'send_user_id' => getUser('id'),
            'type'         => Banhist::CHANGE,
            'reason'       => $request->validated('reason'),
            'term'         => $timeban->getTimestamp() - now()->getTimestamp(),
        ]);

        return redirect($this->editUrl($user))
            ->with('success', __('main.record_changed_success'));
    }

    /**
     * Снятие бана
     */
    public function unban(Request $request): RedirectResponse
    {
        $user = $this->findBannedUser($request);

        $user->update([
            'level'   => User::USER,
            'timeban' => null,
        ]);

        Banhist::query()->create([
            'user_id'      => $user->id,
            'send_user_id' => getUser('id'),
            'type'         => Banhist::UNBAN,
        ]);

        return redirect($this->editUrl($user))
            ->with('success', __('admin.bans.success_unbanned'));
    }

    /**
     * Возвращает пользователя из запроса или 404
     */
    private function findUser(Request $request): User
    {
        $user = User::query()
            ->where('login', $request->string('user')->value())
            ->with('lastBan')
            ->first();

        if (! $user) {
            abort(404, __('validator.user'));
        }

        return $user;
    }

    /**
     * Возвращает пользователя, которого можно забанить
     */
    private function findBannableUser(Request $request): User
    {
        $user = $this->findUser($request);

        if (in_array($user->level, User::ADMIN_GROUPS, true)) {
            abort(200, __('admin.bans.forbidden_ban'));
        }

        return $user;
    }

    /**
     * Возвращает пользователя с действующим баном
     */
    private function findBannedUser(Request $request): User
    {
        $user = $this->findUser($request);

        if ($user->level !== User::BANNED || ! $user->timeban || $user->timeban->isPast()) {
            abort(200, __('admin.bans.user_not_banned'));
        }

        return $user;
    }

    /**
     * Адрес страницы бана пользователя
     */
    private function editUrl(User $user): string
    {
        return 'admin/bans/edit?' . http_build_query(['user' => $user->login]);
    }
}
