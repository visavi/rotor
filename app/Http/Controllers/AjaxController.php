<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Article;
use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\Down;
use App\Models\File;
use App\Models\Guestbook;
use App\Models\Item;
use App\Models\Message;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Spam;
use App\Models\Sticker;
use App\Models\Wall;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AjaxController extends Controller
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
     * @return View
     */
    public function bbCode(Request $request): View
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

        switch ($type) :
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
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
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

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

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

        if ($request->input('_token') !== csrf_token()) {
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
            'rating' => formatNum($post['rating'])->toHtml(),
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
    public function uploadFile(Request $request, Validator $validator): string
    {
        $imageTypes = [
            Article::$morphName,
            Item::$morphName,
            Photo::$morphName,
        ];

        $fileTypes = [
            Message::$morphName,
        ];

        $id   = int($request->input('id'));
        $file = $request->file('file');
        $type = $request->input('type');

        if (! in_array($type, array_merge($imageTypes, $fileTypes), true)) {
            return json_encode(['status' => 'error', 'message' => 'Type invalid']);
        }

        /** @var BaseModel $class */
        $class = Relation::getMorphedModel($type);
        $isImageType = in_array($type, $imageTypes, true);

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

        $countFiles = File::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUser('id'))
            ->count();

        $validator
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->lt($countFiles, setting('maxfiles'), __('validator.files_max', ['max' => setting('maxfiles')]));

        if ($validator->isValid()) {
            $rules = [
                'minweight'  => 100,
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', setting('file_extensions')),
            ];

            $validator->file($file, $rules, ['files' => __('validator.file_upload_failed')]);
        }

        if ($validator->isValid()) {
            $fileData = $model->uploadFile($file);
            $fileType = $fileData['is_image'] ? 'image' : 'file';

            if ($isImageType) {
                $imageData = resizeProcess($fileData['path'], ['size' => 100]);
                $data = [
                    'status' => 'success',
                    'id'     => $fileData['id'],
                    'path'   => $imageData['path'],
                    'source' => $imageData['source'],
                    'type'   => $fileType,
                ];
            } else {
                $data = [
                    'status' => 'success',
                    'id'     => $fileData['id'],
                    'path'   => $fileData['path'],
                    'name'   => $fileData['name'],
                    'size'   => $fileData['size'],
                    'type'   => $fileType,
                ];
            }

            return json_encode($data);
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
    public function deleteFile(Request $request, Validator $validator): string
    {
        $types = [
            Article::$morphName,
            Item::$morphName,
            Photo::$morphName,
            Message::$morphName,
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

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true(getUser('id') === $file->user_id || isAdmin(), __('ajax.file_not_author'));

        if ($validator->isValid()) {
            $file->delete();

            return json_encode([
                'status' => 'success',
                'path'   => $file->hash,
            ]);
        }

        return json_encode([
            'status'  => 'error',
            'message' => current($validator->getErrors())
        ]);
    }

    /**
     * Вставляет стикер
     *
     * @return string
     * @throws Exception
     */
    public function getStickers(): string
    {
        $stickers = Sticker::query()
            //->where('category_id', $id)
            ->orderBy(DB::raw('CHAR_LENGTH(code)'))
            ->orderBy('name')
            ->get();

        $view = view('pages/_stickers_modal', compact('stickers'));

        return json_encode([
            'status' => 'success',
            'stickers' => $view,
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
