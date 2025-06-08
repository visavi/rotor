<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Бан пользователя
     */
    public function edit(Request $request, Validator $validator): View|RedirectResponse
    {
        $user = User::query()->where('login', $request->input('user'))->with('lastBan')->first();

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if (in_array($user->level, User::ADMIN_GROUPS, true)) {
            abort(200, __('admin.bans.forbidden_ban'));
        }

        if ($request->isMethod('post')) {
            $time = int($request->input('time'));
            $type = $request->input('type');
            $reason = $request->input('reason');
            $notice = $request->input('notice');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->false($user->level === User::BANNED && $user->timeban > SITETIME, __('admin.bans.user_banned'))
                ->gt($time, 0, ['time' => __('admin.bans.time_not_indicated')])
                ->in($type, ['minutes', 'hours', 'days'], ['type' => __('admin.bans.time_not_selected')])
                ->length($reason, 5, 1000, ['reason' => __('validator.text')])
                ->length($notice, 0, 1000, ['notice' => __('validator.text_long')]);

            if ($validator->isValid()) {
                if ($type === 'days') {
                    $time *= 86400;
                } elseif ($type === 'hours') {
                    $time *= 3600;
                } else {
                    $time *= 60;
                }

                $user->update([
                    'level'   => User::BANNED,
                    'timeban' => SITETIME + $time,
                ]);

                Banhist::query()->create([
                    'user_id'      => $user->id,
                    'send_user_id' => getUser('id'),
                    'type'         => Banhist::BAN,
                    'reason'       => $reason,
                    'term'         => $time,
                    'created_at'   => SITETIME,
                ]);

                $user->note()->updateOrCreate([], [
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('admin.bans.success_banned'));

                return redirect('admin/bans/edit?user=' . $user->login);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/bans/edit', compact('user'));
    }

    /**
     * Изменение бана
     */
    public function change(Request $request, Validator $validator): View|RedirectResponse
    {
        $user = User::query()->where('login', $request->input('user'))->with('lastBan')->first();

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if ($user->level !== User::BANNED || $user->timeban < SITETIME) {
            abort(200, __('admin.bans.user_not_banned'));
        }

        if ($request->isMethod('post')) {
            $timeban = int($request->input('timeban'));
            $reason = $request->input('reason');

            $timeban = strtotime($timeban);
            $term = $timeban - SITETIME;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->gt($term, 0, ['timeban' => __('admin.bans.time_empty')])
                ->length($reason, 5, 1000, ['reason' => __('validator.text')]);

            if ($validator->isValid()) {
                $user->update([
                    'level'   => User::BANNED,
                    'timeban' => $timeban,
                ]);

                Banhist::query()->create([
                    'user_id'      => $user->id,
                    'send_user_id' => getUser('id'),
                    'type'         => Banhist::CHANGE,
                    'reason'       => $reason,
                    'term'         => $term,
                    'created_at'   => SITETIME,
                ]);

                setFlash('success', __('main.record_changed_success'));

                return redirect('admin/bans/edit?user=' . $user->login);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/bans/change', compact('user'));
    }

    /**
     * Снятие бана
     */
    public function unban(Request $request, Validator $validator): RedirectResponse
    {
        $user = User::query()->where('login', $request->input('user'))->with('lastBan')->first();

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if ($user->level !== User::BANNED || $user->timeban < SITETIME) {
            abort(200, __('admin.bans.user_not_banned'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $user->update([
                'level'   => User::USER,
                'timeban' => null,
            ]);

            Banhist::query()->create([
                'user_id'      => $user->id,
                'send_user_id' => getUser('id'),
                'type'         => Banhist::UNBAN,
                'created_at'   => SITETIME,
            ]);

            setFlash('success', __('admin.bans.success_unbanned'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/bans/edit?user=' . $user->login);
    }
}
