<?php

namespace App\Controllers\User;

use App\Classes\Request;
use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Banhist;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class BanController extends BaseController
{
    /*
    * Бан пользователя
    */
    function ban()
    {
        if (! $user = getUser()) {
            abort(403, 'Вы не авторизованы!');
        }

        if ($user->level != User::BANNED) {
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

        if ($banhist && Request::isMethod('post')) {
            $msg = check(Request::input('msg'));

            $sendUser = getUserById($banhist->send_user_id);

            $validator = new Validator();
            $validator
                ->true(setting('addbansend'), 'Писать объяснительные запрещено администрацией!')
                ->true($banhist->explain, 'Ошибка! Вы уже писали объяснение!')
                ->true($sendUser->id, 'Пользователь который вас забанил не найден!')
                ->length($msg, 5, 1000, ['text' => 'Слишком длинное или короткое объяснение!']);

            if ($validator->isValid()) {

                $message = 'Объяснение нарушения: '.antimat($msg);

                sendPrivate($sendUser, $user, $message);

                $banhist->update([
                    'explain' => 0
                ]);

                setFlash('success', 'Объяснение успешно отправлено!');
                redirect('/ban');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('user/ban', compact('user', 'banhist'));
    }

    /**
     * Снятие нарушения
     */
    public function unban()
    {
        if (! $user = getUser()) {
            abort(403, 'Вы не авторизованы!');
        }

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['ban', 'change'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $banhist) {
            abort('default', 'Не найдена история банов!');
        }

        $daytime = round(((SITETIME - $banhist->created_at) / 3600) / 24);

        if (Request::isMethod('post')) {
            if ($user->totalban > 0 && $daytime >= 30 && $user->money >= 100000) {

                $banhist->delete();

                $user->update([
                    'totalban' => DB::raw('totalban - 1'),
                    'money'    => DB::raw('money - 100000'),
                ]);

                setFlash('success', 'Нарушение успешно снято!');
            } else {
                setFlash('danger', 'У вас нет нарушений, не прошло еще 30 суток или недостаточная сумма на счете');
            }

            redirect('/unban');
        }

        return view('user/unban', compact('user', 'daytime'));
    }

    /**
     * История банов
     */
    public function banhist()
    {
        if (! getUser()) {
            abort('default', 'Для просмотра истории банов необходимо авторизоваться!');
        }

        $login = check(Request::input('user', getUser('login')));

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Banhist::query()->where('user_id', $user->id)->count();
        $page = paginate(setting('listbanhist'), $total);

        $banhist = Banhist::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('user/banhist', compact('user', 'banhist', 'page'));
    }
}
