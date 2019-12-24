<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Guestbook;
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
        $posts = Guestbook::query()
            ->orderByDesc('created_at')
            ->with('user', 'editUser')
            ->paginate(setting('bookpost'));

        return view('guestbooks/index', compact('posts'));
    }

    /**
     * Добавление сообщения
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return void
     */
    public function add(Request $request, Validator $validator, Flood $flood): void
    {
        $msg       = check($request->input('msg'));
        $token     = check($request->input('token'));
        $guestName = check($request->input('guest_name'));

        $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
            ->length($msg, 5, setting('guesttextlength'), ['msg' => __('validator.text')])
            ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

        /* Проверка для гостей */
        if (! getUser() && setting('bookadds')) {
            $validator->true(captchaVerify(), ['protect' => __('validator.captcha')]);
            $validator->false(strpos($msg, '//'), ['msg' => __('guestbooks.without_links')]);
            $validator->length($guestName, 3, 20, ['guest_name' => __('users.name_short_or_long')], false);
        } else {
            $validator->true(getUser(), ['msg' => __('main.not_authorized')]);
        }

        if ($validator->isValid()) {
            $msg = antimat($msg);

            if (getUser()) {
                $guestName  = null;
                $bookscores = setting('bookscores') ? 1 : 0;

                getUser()->increment('allguest');
                getUser()->increment('point', $bookscores);
                getUser()->increment('money', 5);
            }

            Guestbook::query()->create([
                'user_id'    => getUser('id'),
                'text'       => $msg,
                'ip'         => getIp(),
                'brow'       => getBrowser(),
                'guest_name' => $guestName,
                'created_at' => SITETIME,
            ]);

            clearCache('statGuestbooks');
            $flood->saveState();

            sendNotify($msg, '/guestbooks', __('index.guestbooks'));
            setFlash('success', __('main.message_added_success'));
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
     *
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
            abort('default', __('main.message_not_found'));
        }

        if ($post->created_at + 600 < SITETIME) {
            abort('default', __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 5, setting('guesttextlength'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('main.message_edited_success'));
                redirect('/guestbooks');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('guestbooks/edit', compact('post'));
    }
}
