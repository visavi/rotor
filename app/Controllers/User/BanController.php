<?php

namespace App\Controllers\User;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Http\Request;

class BanController extends BaseController
{
    /**
     * Бан пользователя
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function ban(Request $request, Validator $validator): string
    {
        if (! $user = getUser()) {
            abort(403, 'Вы не авторизованы!');
        }

        if ($user->level !== User::BANNED) {
            abort('default', 'Вы не забанены или срок бана истек!');
        }

        if ($user->timeban <= SITETIME) {

            $user->update([
                'level'   => User::USER,
                'timeban' => 0,
            ]);

            setFlash('success', 'Поздравляем! Время вашего бана истекло!');
            redirect('/');
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($banhist && $request->isMethod('post')) {
            $msg = check($request->input('msg'));

            $sendUser = getUserById($banhist->send_user_id);

            $validator
                ->true(setting('addbansend'), 'Писать объяснительные запрещено администрацией!')
                ->true($banhist->explain, 'Ошибка! Вы уже писали объяснение!')
                ->true($sendUser->id, 'Пользователь который вас забанил не найден!')
                ->length($msg, 5, 1000, ['text' => trans('validator.text')]);

            if ($validator->isValid()) {

                $message = 'Объяснение нарушения: '.antimat($msg);

                $sendUser->sendMessage($user, $message);

                $banhist->update([
                    'explain' => 0
                ]);

                setFlash('success', 'Объяснение успешно отправлено!');
                redirect('/ban');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/bans', compact('user', 'banhist'));
    }
}
