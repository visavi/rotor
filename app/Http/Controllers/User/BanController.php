<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\BanExplainRequest;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BanController extends Controller
{
    /**
     * User Ban
     */
    public function ban(): View|RedirectResponse
    {
        $user = $this->bannedUser();

        if (! $user->timeban || $user->timeban->lte(now())) {
            $user->update([
                'level'   => User::USER,
                'timeban' => null,
            ]);

            return redirect('/')
                ->with('success', __('users.ban_expired'));
        }

        $banhist = $this->lastBan($user);

        return view('users/bans', compact('user', 'banhist'));
    }

    /**
     * Объяснение администрации
     */
    public function explain(BanExplainRequest $request): RedirectResponse
    {
        $user = $this->bannedUser();
        $banhist = $this->lastBan($user);

        $error = match (true) {
            ! $banhist                  => __('users.not_banned'),
            ! setting('addbansend')     => __('users.explain_forbidden'),
            (bool) $banhist->explain    => __('users.explain_repeat'),
            ! $this->admins()->exists() => __('users.admins_not_found'),
            default                     => null,
        };

        if ($error || ! $banhist) {
            return redirect('ban')
                ->withInput()
                ->withErrors($error);
        }

        $text = textNotice('explain', ['message' => $request->validated('msg')]);

        foreach ($this->admins()->get() as $admin) {
            $admin->sendMessage($user, $text, false);
        }

        $banhist->update([
            'explain' => 1,
        ]);

        return redirect('ban')
            ->with('success', __('users.explain_sent_success'));
    }

    /**
     * Текущий пользователь, если он забанен
     */
    private function bannedUser(): User
    {
        if (! $user = auth()->user()) {
            abort(403, __('main.not_authorized'));
        }

        if ($user->level !== User::BANNED) {
            abort(200, __('users.not_banned'));
        }

        return $user;
    }

    /**
     * Администрация, которой уходит объяснение
     *
     * @return Builder<User>
     */
    private function admins(): Builder
    {
        return User::query()->whereIn('level', [User::BOSS, User::ADMIN]);
    }

    /**
     * Последняя запись о бане или его изменении
     */
    private function lastBan(User $user): ?Banhist
    {
        return Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderByDesc('created_at')
            ->first();
    }
}
