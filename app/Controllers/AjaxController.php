<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Guest;
use App\Models\Inbox;
use App\Models\News;
use App\Models\Photo;
use App\Models\Polling;
use App\Models\Post;
use App\Models\Spam;
use Illuminate\Database\Capsule\Manager as DB;

class AjaxController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

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

        return view('app/_bbcode', compact('message'));
    }

    /**
     * Жалоба на сообщение
     */
    public function complaint()
    {
        $path  = null;
        $data  = false;
        $id    = abs(intval(Request::input('id')));
        $type  = Request::input('type');
        $page  = check(Request::input('page'));
        $token = check(Request::input('token'));

        switch ($type):
            case News::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/news/'.$data['relate_id'].'/comments?page='.$page;
                break;

            case Blog::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/blog?page='.$page;
                break;

            case Photo::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/gallery/'.$data['relate_id'].'/comments?page='.$page;
                break;

            case Guest::class:
                $data = $type::query()->find($id);
                $path = '/book?page='.$page;
                break;

            case Post::class:
                $data = $type::query()->find($id);
                $path = '/topic/'.$data['topic_id'].'?page='.$page;
                break;

            case Inbox::class:
                $data = $type::query()->find($id);
                break;
        endswitch;

        $spam = Spam::query()->where(['relate_type' => $type, 'relate_id' => $id])->first();

        $validation = new Validation();
        $validation
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', isUser(), 'Для отправки жалобы необходимо авторизоваться')
            ->addRule('bool', $data, 'Выбранное вами сообщение для жалобы не существует!')
            ->addRule('bool', ! $spam, 'Жалоба на данное сообщение уже отправлена!');

        if ($validation->run()) {
            Spam::query()->create([
                'relate_type' => $type,
                'relate_id'   => $data['id'],
                'user_id'     => getUserId(),
                'path'        => $path,
                'created_at'  => SITETIME,
            ]);

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
            $delComments = Comment::query()
                ->where('relate_type', $type)
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
        $types = [
            Post::class,
            Blog::class,
            News::class,
            Photo::class,
        ];

        $id    = abs(intval(Request::input('id')));
        $type  = Request::input('type');
        $vote  = intval(Request::input('vote'));
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

        if (! in_array($type, $types, true)) {
            exit(json_encode(['status' => 'error', 'message' => 'Type invalid']));
        }

        Polling::query()
            ->where('relate_type', $type)
            ->where('created_at', '<', SITETIME)
            ->delete();

        $post = $type::query()->where('user_id', '<>', getUserId())->find($id);
        if (! $post) {
            exit(json_encode([
                'status' => 'error',
                'message' => 'message not found',
            ]));
        }

        $polling = Polling::query()
            ->where('relate_type', $type)
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
            Polling::query()->create([
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
