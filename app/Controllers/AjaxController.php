<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\{BaseModel, Article, Comment, Down, File, Guestbook, Message, Item, News, Offer, Photo, Post, Spam, Wall};
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class AjaxController extends BaseController
{
    /**
     * Конструктор
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->checkAjax($request);
        $this->checkAuthorize();
    }

    /**
     * Возвращает bbCode для предпросмотра
     *
     * @param Request $request
     *
     * @return string
     */
    public function bbCode(Request $request): string
    {
        $message = $request->input('data');

        return view('app/_bbcode', compact('message'));
    }

    /**
     * Отправляет жалобу на сообщение
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function complaint(Request $request, Validator $validator): string
    {
        $path  = null;
        $model = false;
        $id    = int($request->input('id'));
        $type  = $request->input('type');
        $page  = $request->input('page');

        switch ($type):
            case Guestbook::$morphName:
                $model = Guestbook::query()->find($id);
                $path = '/guestbook?page='.$page;
                break;

            case Post::$morphName:
                $model = Post::query()->find($id);
                $path = '/topics/' . $model->topic_id . '?page='.$page;
                break;

            case Message::$morphName:
                $model = Message::query()->find($id);
                break;

            case Wall::$morphName:
                $model = Wall::query()->find($id);
                $path = '/walls/' . $model->user->login . '?page='.$page;
                break;

            case News::$morphName:
            case Article::$morphName:
            case Photo::$morphName:
            case Offer::$morphName:
            case Down::$morphName:
                $model = Comment::query()->find($id);
                $path = '/' . $model->relate_type . '/comments/' . $model->relate_id . '?page='.$page;
                $type = 'comments';
                break;
        endswitch;

        $spam = Spam::query()->where(['relate_type' => $type, 'relate_id' => $id])->first();

        $validator
            ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->true($model, __('main.message_not_found'))
            ->false($spam, __('ajax.complaint_already_sent'));

        if ($validator->isValid()) {
            Spam::query()->create([
                'relate_type' => $type,
                'relate_id'   => $model->id,
                'user_id'     => getUser('id'),
                'path'        => $path,
                'created_at'  => SITETIME,
            ]);

            return json_encode(['status' => 'success']);
        }

        return json_encode([
            'status'  => 'error',
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Удаляет комментарии
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function delComment(Request $request, Validator $validator): string
    {
        if (! isAdmin()) {
            return json_encode([
                'status'  => 'error',
                'message' => __('main.not_authorized'),
            ]);
        }

        $type = $request->input('type');
        $rid  = int($request->input('rid'));
        $id   = int($request->input('id'));

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'));

        if ($validator->isValid()) {
            $delComments = Comment::query()
                ->where('relate_type', $type)
                ->where('relate_id', $rid)
                ->where('id', $id)
                ->delete();

            if ($delComments) {
                /** @var BaseModel $class */
                $class = Relation::getMorphedModel($type);
                $model = $class::query()->find($rid);

                if ($model) {
                    $model->decrement('count_comments');
                }
            }

            return json_encode(['status' => 'success']);
        }

        return json_encode([
            'status'  => 'error',
            'message' => current($validator->getErrors())
        ]);
    }

    /**
     * Изменяет рейтинг
     *
     * @param Request $request
     *
     * @return string
     * @throws Exception
     */
    public function rating(Request $request): string
    {
        $types = [
            Post::$morphName,
            Article::$morphName,
            Photo::$morphName,
            Offer::$morphName,
            News::$morphName,
        ];

        $id   = int($request->input('id'));
        $type = $request->input('type');
        $vote = $request->input('vote');

        if ($request->input('token') !== $_SESSION['token']) {
            return json_encode(['status' => 'error', 'message' => 'Invalid token']);
        }

        if (! in_array($vote, ['+', '-'], true)) {
            return json_encode(['status' => 'error', 'message' => 'Invalid rating']);
        }

        if (! in_array($type, $types, true)) {
            return json_encode(['status' => 'error', 'message' => 'Type invalid']);
        }

        /** @var BaseModel $model */
        $model = Relation::getMorphedModel($type);

        $post = $model::query()
            ->where('id', $id)
            ->where('user_id', '<>', getUser('id'))
            ->first();

        if (! $post) {
            return json_encode(['status' => 'error', 'message' => 'Record not found']);
        }

        $polling = $post->polling()->first();
        $cancel = false;

        if ($polling) {
            if ($polling->vote === $vote) {
                return json_encode(['status' => 'error']);
            }

            $polling->delete();
            $cancel = true;
        } else {
            $post->polling()->create([
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
     * Загружает изображение
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function uploadImage(Request $request, Validator $validator): string
    {
        $types = [
            Article::$morphName,
            Item::$morphName,
            Photo::$morphName,
        ];

        $id    = int($request->input('id'));
        $image = $request->file('image');
        $type  = $request->input('type');

        if (! in_array($type, $types, true)) {
            return json_encode(['status' => 'error', 'message' => 'Type invalid']);
        }

        /** @var BaseModel $class */
        $class = Relation::getMorphedModel($type);

        if ($id) {
            $model = $class::query()->where('user_id', getUser('id'))->find($id);

            if (! $model) {
                return json_encode([
                    'status'  => 'error',
                    'message' => 'Service not found'
                ]);
            }
        } else {
            $model = new $class();
        }

        $countFiles = $model->files()
            ->where('user_id', getUser('id'))
            ->count();

        $validator
            ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->lt($countFiles, setting('maxfiles'), __('validator.files_max', ['max' => setting('maxfiles')]));

        if ($validator->isValid()) {
            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];

            $validator->file($image, $rules, ['files' => __('validator.image_upload_failed')]);
        }

        if ($validator->isValid()) {
            $file  = $model->uploadFile($image);
            $image = resizeProcess($file['path'], ['size' => 100]);

            return json_encode([
                'status' => 'success',
                'id'     => $file['id'],
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
     * Удаляет изображение
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     * @throws Exception
     */
    public function deleteImage(Request $request, Validator $validator): string
    {
        $types = [
            Article::$morphName,
            Item::$morphName,
            Photo::$morphName,
        ];

        $id   = int($request->input('id'));
        $type = $request->input('type');

        if (! in_array($type, $types, true)) {
            return json_encode(['status' => 'error', 'message' => 'Type invalid']);
        }

        /** @var File $file */
        $file = File::query()
            ->where('relate_type', $type)
            ->find($id);

        if (! $file) {
            return json_encode([
                'status'  => 'error',
                'message' => 'File not found'
            ]);
        }

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->true(getUser('id') === $file->user_id || isAdmin(), __('ajax.image_not_author'))
            ->true(! $file->relate_id || isAdmin(), __('ajax.image_delete_attached'));

        if ($validator->isValid()) {
            $file->delete();

            return json_encode([
                'status' => 'success',
            ]);
        }

        return json_encode([
            'status'  => 'error',
            'message' => current($validator->getErrors())
        ]);
    }

    /**
     * Возвращает является ли запрос ajax
     *
     * @param Request $request
     *
     * @return mixed
     */
    private function checkAjax(Request $request)
    {
        if (! $request->ajax()) {
            exit(json_encode([
                'status'  => 'error',
                'message' => __('validator.not_ajax')
            ]));
        }

        return true;
    }

    /**
     * Возвращает авторизован ли пользователь
     *
     * @return mixed
     */
    private function checkAuthorize()
    {
        if (! getUser()) {
            exit(json_encode([
                'status'  => 'error',
                'message' => __('main.not_authorized')
            ]));
        }

        return true;
    }
}
