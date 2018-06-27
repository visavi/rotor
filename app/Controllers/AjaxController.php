<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Blog;
use App\Models\Board;
use App\Models\Comment;
use App\Models\Down;
use App\Models\File;
use App\Models\Guestbook;
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
        $this->checkAjax();
        $this->checkAuthorize();
    }

    /**
     * Проверяет является ли запрос ajax
     */
    public function checkAjax()
    {
        if (! Request::ajax()) {
            return json_encode([
                'status' => 'error',
                'message' => 'This is not ajax request'
            ]);
        }
    }

    /**
     * Проверяет является ли запрос ajax
     */
    public function checkAuthorize()
    {
        if (! getUser()) {
            return json_encode([
                'status' => 'error',
                'message' => 'Not authorized'
            ]);
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
            case Guestbook::class:
                $data = $type::query()->find($id);
                $path = '/guestbooks?page='.$page;
                break;

            case Post::class:
                $data = $type::query()->find($id);
                $path = '/topics/' . $data->topic_id . '?page='.$page;
                break;

            case Inbox::class:
                $data = $type::query()->find($id);
                break;

            case Wall::class:
                $data = $type::query()->find($id);
                $path = '/walls/' . $data->user->login . '?page='.$page;
                break;

            case News::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $type = Comment::class;
                $path = '/news/comments/' . $data->relate_id . '?page='.$page;
                break;

            case Blog::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $type = Comment::class;
                $path = '/articles/comments/' . $data->relate_id . '?page=' . $page;
                break;

            case Photo::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $type = Comment::class;
                $path = '/photos/comments/' . $data->relate_id . '?page='.$page;
                break;

            case Offer::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $type = Comment::class;
                $path = '/offers/comments/' . $data->relate_id . '?page='.$page;
                break;

            case Down::class:
                $data = Comment::query()
                    ->where('relate_type', $type)
                    ->where('id', $id)
                    ->first();
                $type = Comment::class;
                $path = '/downs/comments/' . $data->relate_id . '?page='.$page;
                break;
        endswitch;

        $spam = Spam::query()->where(['relate_type' => $type, 'relate_id' => $id])->first();

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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

            return json_encode(['status' => 'success']);
        }

        return json_encode([
            'status' => 'error',
            'message' => current($validator->getErrors())
        ]);
    }

    /**
     * Удаление комментариев
     */
    public function delComment()
    {
        if (! isAdmin()) {
            return json_encode(['status' => 'error', 'message' => 'Not authorized']);
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

            return json_encode(['status' => 'success']);
        }

        return json_encode([
            'status' => 'error',
            'message' => current($validator->getErrors())
        ]);
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

        if ($token !== $_SESSION['token']) {
            return json_encode(['status' => 'error', 'message' => 'Invalid token']);
        }

        if (! in_array($vote, ['+', '-'], true)) {
            return json_encode(['status' => 'error', 'message' => 'Invalid rating']);
        }

        if (! in_array($type, $types, true)) {
            return json_encode(['status' => 'error', 'message' => 'Type invalid']);
        }

        $post = $type::query()
            ->where('user_id', '<>', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $post) {
            return json_encode(['status' => 'error', 'message' => 'Record not found']);
        }

        $polling = Polling::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUser('id'))
            ->first();

        $cancel = false;

        if ($polling) {
            if ($polling->vote === $vote) {
                return json_encode(['status' => 'error']);
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

        return json_encode([
            'status' => 'success',
            'cancel' => $cancel,
            'rating' => formatNum($post['rating'])
        ]);
    }

    /**
     * Загрузка изображений
     */
    public function uploadImage()
    {
        $types = [
            Blog::class,
            Board::class,
        ];

        $image = Request::file('image');
        $id    = int(Request::input('id'));
        $type  = check(Request::input('type'));
        $token = check(Request::input('token'));

        if (! in_array($type, $types, true)) {
            return json_encode(['status' => 'error', 'message' => 'Type invalid']);
        }

        if ($id) {
            $model = $type::query()->where('user_id', getUser('id'))->find($id);

            if (! $model) {
                return json_encode([
                    'status'  => 'error',
                    'message' => 'Service not found'
                ]);
            }
        } else {
            $model = new $type();
        }

        $countFiles = File::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUser('id'))
            ->count();

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->lt($countFiles, setting('maxfiles'), 'Разрешено загружать не более ' . setting('maxfiles') . ' файлов!');

        if ($validator->isValid()) {
            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];

            $validator->file($image, $rules, ['files' => 'Не удалось загрузить фотографию!']);
        }

        if ($validator->isValid()) {
            $upload = $model->uploadFile($image);
            $image  = resizeProcess($upload, ['size' => 100]);

            return json_encode([
                'status' => 'success',
                'path'   => $image['path'],
                'source' => $image['source'],
            ]);
        }

        return json_encode([
            'status'  => 'error',
            'message' => current($validator->getErrors())
        ]);
    }

    /**
     * Удаление изображений
     */
    public function deleteImage()
    {
        $id    = int(Request::input('id'));
        $type  = check(Request::input('type'));
        $token = check(Request::input('token'));

        $file = File::query()
            ->where('relate_type', $type)
            ->find($id);

        if (! $file) {
            return json_encode([
                'status'  => 'error',
                'message' => 'File not found'
            ]);
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($file->user_id === getUser('id') || isAdmin(), 'Удаление невозможно, вы не автор данного файла!');

        if ($validator->isValid()) {

            deleteFile(UPLOADS . '/' . $file->hash);
            $file->delete();

            return json_encode([
                'status'  => 'success',
            ]);
        }

        return json_encode([
            'status'  => 'error',
            'message' => current($validator->getErrors())
        ]);
    }
}
