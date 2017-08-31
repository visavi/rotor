<?php

namespace App\Controllers;

class AjaxController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        if (! Request::ajax()) {
            exit(json_encode([
                'status' => 'error',
                'message' => 'This is not ajax request'
            ]));
        }
    }

    /**
     * Предпросмотр bbCode
     */
    public function bbCode()
    {
        $message = check(Request::input('data'));

        return view('app/bbcode', compact('message'));
    }

    /**
     * Жалоба на сообщение
     */
    public function complaint()
    {
        $path  = null;
        $data  = false;
        $id    = abs(intval(Request::input('id')));
        $type  = check(Request::input('type'));
        $page  = check(Request::input('page'));
        $token = check(Request::input('token'));

        switch ($type):
            case 'News':
                $data = Comment::where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/news/'.$data['relate_id'].'/comments?page='.$page;
                break;

            case 'Blog':
                $data = Comment::where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/blog?page='.$page;
                break;

            case 'Photo':
                $data = Comment::where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/gallery/'.$data['relate_id'].'/comments?page='.$page;
                break;

            case 'Guest':
                $data = $type::find($id);
                $path = '/book?page='.$page;
                break;

            case 'Post':
                $data = $type::find($id);
                $path = '/topic/'.$data['topic_id'].'?page='.$page;
                break;

            case 'Inbox':
                $data = $type::find($id);
                break;
        endswitch;

        $spam = Spam::where(['relate_type' => $type, 'relate_id' => $id])->first();

        $validation = new Validation();
        $validation
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', isUser(), 'Для отправки жалобы необходимо авторизоваться')
            ->addRule('bool', $data, 'Выбранное вами сообщение для жалобы не существует!')
            ->addRule('bool', ! $spam, 'Жалоба на данное сообщение уже отправлена!');

        if ($validation->run()) {
            $spam = new Spam();
            $spam->relate_type = $type;
            $spam->relate_id   = $data['id'];
            $spam->user_id     = getUserId();
            $spam->path        = $path;
            $spam->created_at  = SITETIME;
            $spam->save();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validation->getErrors())
            ]);
        }
    }

    /**
     * Удаление комментариев
     */
    public function delComment()
    {
        if (! isAdmin()) {
            exit(json_encode(['status' => 'error', 'message' => 'Not authorized']));
        }

        $token = check(Request::input('token'));
        $type  = check(Request::input('type'));
        $rid   = abs(intval(Request::input('rid')));
        $id    = abs(intval(Request::input('id')));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

        if ($validation->run()) {
            $delComments = Comment::where('relate_type', $type)
                ->where('relate_id', $rid)
                ->where('id', $id)
                ->delete();

            if ($delComments) {
                $type::where('id', $rid)
                    ->update([
                        'comments'  => DB::raw('comments - '.$delComments),
                    ]);
            }

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validation->getErrors())
            ]);
        }
    }

    /**
     * Изменение рейтинга
     */
    public function rating()
    {
        $id    = abs(intval(Request::input('id')));
        $type  = check(Request::input('type'));
        $vote  = check(Request::input('vote'));
        $token = check(Request::input('token'));

        // Время хранения голосов
        $expiresRating = SITETIME + 3600 * 24 * 30;

        if (! isUser()) {
            exit(json_encode(['status' => 'error', 'message' => 'Not authorized']));
        }

        if ($token != $_SESSION['token']) {
            exit(json_encode(['status' => 'error', 'message' => 'Invalid token']));
        }

        if (! in_array($vote, [-1, 1])) {
            exit(json_encode(['status' => 'error', 'message' => 'Invalid rating']));
        }

        Polling::where('relate_type', $type)
            ->where('created_at', '<', SITETIME)
            ->delete();

        $post = $type::where('user_id', '<>', getUserId())->find($id);
        if (! $post) {
            exit(json_encode([
                'status' => 'error',
                'message' => 'message not found',
            ]));
        }

        $polling = Polling::where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUserId())
            ->first();

        $cancel = false;

        if ($polling) {
            if ($polling['vote'] == $vote) {
                exit(json_encode(['status' => 'error']));
            } else {

                $polling->delete();
                $cancel = true;
            }
        } else {
            Polling::create([
                'relate_type' => $type,
                'relate_id'   => $id,
                'user_id'     => getUserId(),
                'vote'        => $vote,
                'created_at'  => $expiresRating,
            ]);
        }

        $operation = ($vote == '1') ? '+' : '-';

        $post->update(['rating' => DB::raw("rating $operation 1")]);
        $post = $type::find($id);

        echo json_encode([
            'status' => 'success',
            'cancel' => $cancel,
            'rating' => formatNum($post['rating'])
        ]);
    }
}
