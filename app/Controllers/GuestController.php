<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class GuestController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Guest::query()->count();
        $page = paginate(setting('bookpost'), $total);

        $posts = Guest::query()
            ->orderBy('created_at', 'desc')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->with('user', 'editUser')
            ->get();

        return view('guest/index', compact('posts', 'page'));
    }

    /**
     * Добавление сообщения
     */
    public function add()
    {
        $msg   = check(Request::input('msg'));
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->length($msg, 5, setting('guesttextlength'), ['msg' => 'Слишком длинное или короткое сообщение!'])
            ->true(Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

        /* Проерка для гостей */
        if (! getUser() && setting('bookadds')) {
            $validator->true(captchaVerify(), ['protect' => 'Не удалось пройти проверку captcha!']);
        } else {
            $validator->true(getUser(), ['msg' => 'Для добавления сообщения необходимо авторизоваться']);
        }

        if ($validator->isValid()) {

            $msg = antimat($msg);

            if (getUser()) {
                $bookscores = (setting('bookscores')) ? 1 : 0;

                $user = User::query()->where('id', getUser('id'));
                $user->update([
                    'allguest' => DB::raw('allguest + 1'),
                    'point'    => DB::raw('point + ' . $bookscores),
                    'money'    => DB::raw('money + 5'),
                ]);
            }

            $username = getUser() ? getUser('id') : 0;

            Guest::query()->create([
                'user_id'    => $username,
                'text'       => $msg,
                'ip'         => getIp(),
                'brow'       => getBrowser(),
                'created_at' => SITETIME,
            ]);

            setFlash('success', 'Сообщение успешно добавлено!');
        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/book');
    }

    /**
     * Редактирование сообщения
     */
    public function edit($id)
    {
        if (! getUser()) {
            abort(403);
        }

        $post = Guest::query()->where('user_id', getUser('id'))->find($id);

        if (! $post) {
            abort('default', 'Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }

        if ($post->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {

            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 5, setting('guesttextlength'), ['msg' => 'Слишком длинное или короткое сообщение!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/book');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('guest/edit', compact('post'));
    }
}
