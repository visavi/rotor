<?php

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Guestbook;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

class GuestbookController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $total = Guestbook::query()->count();
        $page = paginate(setting('bookpost'), $total);

        $posts = Guestbook::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user', 'editUser')
            ->get();

        return view('guestbooks/index', compact('posts', 'page'));
    }

    /**
     * Добавление сообщения
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function add(Request $request, Validator $validator): void
    {
        $msg   = check($request->input('msg'));
        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], ['msg' => trans('validator.token')])
            ->length($msg, 5, setting('guesttextlength'), ['msg' => trans('validator.text')])
            ->true(Flood::isFlood(), ['msg' => trans('validator.flood', ['sec' => Flood::getPeriod()])]);

        /* Проерка для гостей */
        if (! getUser() && setting('bookadds')) {
            $validator->true(captchaVerify(), ['protect' => trans('validator.captcha')]);
            $validator->false(stripos($msg, 'http'), ['msg' => 'Текст сообщения не должен содержать ссылок!']);
        } else {
            $validator->true(getUser(), ['msg' => 'Для добавления сообщения необходимо авторизоваться']);
        }

        if ($validator->isValid()) {

            $msg = antimat($msg);

            if (getUser()) {
                $bookscores = setting('bookscores') ? 1 : 0;

                getUser()->update([
                    'allguest' => DB::connection()->raw('allguest + 1'),
                    'point'    => DB::connection()->raw('point + ' . $bookscores),
                    'money'    => DB::connection()->raw('money + 5'),
                ]);
            }

            $username = getUser() ? getUser('id') : 0;

            Guestbook::query()->create([
                'user_id'    => $username,
                'text'       => $msg,
                'ip'         => getIp(),
                'brow'       => getBrowser(),
                'created_at' => SITETIME,
            ]);

            sendNotify($msg, '/guestbooks', 'Гостевая книга');

            setFlash('success', 'Сообщение успешно добавлено!');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/guestbooks');
    }

    /**
     * Редактирование сообщения
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        if (! getUser()) {
            abort(403);
        }

        /** @var Guestbook $post */
        $post = Guestbook::query()->where('user_id', getUser('id'))->find($id);

        if (! $post) {
            abort('default', 'Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }

        if ($post->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if ($request->isMethod('post')) {

            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => trans('validator.token')])
                ->length($msg, 5, setting('guesttextlength'), ['msg' => trans('validator.text')]);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/guestbooks');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('guestbooks/edit', compact('post'));
    }
}
