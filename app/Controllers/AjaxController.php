<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Guest;
use App\Models\Inbox;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use App\Models\Polling;
use App\Models\Post;
use App\Models\Spam;
use App\Models\Wall;

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
        $id    = int(Request::input('id'));
        $type  = check(Request::input('type'));
        $page  = check(Request::input('page'));
        $token = check(Request::input('token'));

        switch ($type):
            case News::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/news/comments/' . $data->relate_id . '?page='.$page;
                break;

            case Blog::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/blog?page=' . $page;
                break;

            case Photo::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/gallery/comments/' . $data->relate_id . '?page='.$page;
                break;

            case Offer::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/offers/comments/' . $data->relate_id . '?page='.$page;
                break;

            case Guest::class:
                $data = $type::query()->find($id);
                $path = '/book?page='.$page;
                break;

            case Post::class:
                $data = $type::query()->find($id);
                $path = '/topic/' . $data->topic_id . '?page='.$page;
                break;

            case Inbox::class:
                $data = $type::query()->find($id);
                break;

            case Wall::class:
                $data = $type::query()->find($id);
                $path = '/wall/' . $data->user->login . '?page='.$page;
                break;

            case Down::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $path = '/down/' . $data->relate_id . '?page='.$page;
                break;
        endswitch;

        $spam = Spam::query()->where(['relate_type' => $type, 'relate_id' => $id])->first();

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(getUser(), 'Для отправки жалобы необходимо авторизоваться')
            ->true($data, 'Выбранное вами сообщение для жалобы не существует!')
            ->false($spam, 'Жалоба на данное сообщение уже отправлена!');

        if ($validator->isValid()) {
            Spam::query()->create([
                'relate_type' => $type,
                'relate_id'   => $data->id,
                'user_id'     => getUser('id'),
                'path'        => $path,
                'created_at'  => SITETIME,
            ]);

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
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
        $rid   = int(Request::input('rid'));
        $id    = int(Request::input('id'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {
            $delComments = Comment::query()
                ->where('relate_type', $type)
                ->where('relate_id', $rid)
                ->where('id', $id)
                ->delete();

            if ($delComments) {
                $type::query()->find($rid)->decrement('count_comments');
            }

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
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
            Offer::class,
        ];

        $id    = int(Request::input('id'));
        $type  = check(Request::input('type'));
        $vote  = check(Request::input('vote'));
        $token = check(Request::input('token'));

        if (! getUser()) {
            exit(json_encode(['status' => 'error', 'message' => 'Not authorized']));
        }

        if ($token != $_SESSION['token']) {
            exit(json_encode(['status' => 'error', 'message' => 'Invalid token']));
        }

        if (! in_array($vote, ['+', '-'], true)) {
            exit(json_encode(['status' => 'error', 'message' => 'Invalid rating']));
        }

        if (! in_array($type, $types, true)) {
            exit(json_encode(['status' => 'error', 'message' => 'Type invalid']));
        }

        $post = $type::query()
            ->where('user_id', '<>', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $post) {
            exit(json_encode(['status' => 'error', 'message' => 'Record not found']));
        }

        $polling = Polling::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUser('id'))
            ->first();

        $cancel = false;

        if ($polling) {
            if ($polling->vote == $vote) {
                exit(json_encode(['status' => 'error']));
            }

            $polling->delete();
            $cancel = true;
        } else {
            Polling::query()->create([
                'relate_type' => $type,
                'relate_id'   => $id,
                'user_id'     => getUser('id'),
                'vote'        => $vote,
                'created_at'  => SITETIME,
            ]);
        }

        if ($vote === '+') {
            $post->increment('rating');
        } else {
            $post->decrement('rating');
        }

        echo json_encode([
            'status' => 'success',
            'cancel' => $cancel,
            'rating' => formatNum($post['rating'])
        ]);
    }

    /**
     * Загрузка фотографии
     */
/*    public function uploadImage()
    {
        $image = Request::file('image');

        echo sprintf('<img src="data:image/png;base64,%s" />', base64_encode(file_get_contents($image->getPathname())));

        exit(json_encode(['status' => 'success']));

    }*/
}
