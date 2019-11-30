<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Http\Request;

class BanController extends BaseController
{
    /**
     * User Ban
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function ban(Request $request, Validator $validator): string
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($user->level !== User::BANNED) {
            abort('default', __('users.not_banned'));
        }

        if ($user->timeban <= SITETIME) {
            $user->update([
                'level'   => User::USER,
                'timeban' => 0,
            ]);

            setFlash('success', __('users.ban_expired'));
            redirect('/');
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderByDesc('created_at')
            ->first();

        if ($banhist && $request->isMethod('post')) {
            $msg = check($request->input('msg'));

            $admins = User::query()->whereIn('level', [User::BOSS, User::ADMIN])->get();

            $validator
                ->true(setting('addbansend'), __('users.explain_forbidden'))
                ->false($banhist->explain, __('users.explain_repeat'))
                ->true($admins->isNotEmpty(), __('users.admins_not_found'))
                ->length($msg, 5, 1000, ['text' => __('validator.text')]);

            if ($validator->isValid()) {
                $text = textNotice('explain', ['message' => antimat($msg)]);

                foreach ($admins as $admin) {
                    $admin->sendMessage($user, $text);
                }

                $banhist->update([
                    'explain' => 1
                ]);

                setFlash('success', __('users.explain_sent_success'));
                redirect('/ban');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/bans', compact('user', 'banhist'));
    }
}
