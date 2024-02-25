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
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AjaxController extends Controller
{
    /**
     * Возвращает bbCode для предпросмотра
     */
    public function bbCode(Request $request): View
    {
        $message = (string) $request->input('data');

        return view('app/_bbcode', compact('message'));
    }

    /**
     * Отправляет жалобу на сообщение
     */
    public function complaint(Request $request, Validator $validator): JsonResponse
    {
        $path = null;
        $model = false;
        $id = int($request->input('id'));
        $type = $request->input('type');
        $page = $request->input('page');

        switch ($type) :
            case Guestbook::$morphName:
                $model = Guestbook::query()->find($id);
                $path = '/guestbook?page=' . $page;
                break;

            case Post::$morphName:
                $model = Post::query()->find($id);
                $path = '/topics/' . $model->topic_id . '?page=' . $page;
                break;

            case Message::$morphName:
                $model = Message::query()->find($id);
                break;

            case Wall::$morphName:
                $model = Wall::query()->find($id);
                $path = '/walls/' . $model->user->login . '?page=' . $page;
                break;

            case News::$morphName:
            case Article::$morphName:
            case Photo::$morphName:
            case Offer::$morphName:
            case Down::$morphName:
                $model = Comment::query()->find($id);
                $path = '/' . $model->relate_type . '/comments/' . $model->relate_id . '?page=' . $page;
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

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Удаляет комментарии
     */
    public function delComment(Request $request, Validator $validator): JsonResponse
    {
        if (! isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('main.not_authorized'),
            ]);
        }

        $type = $request->input('type');
        $rid = int($request->input('rid'));
        $id = int($request->input('id'));

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

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => 'false',
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Изменяет рейтинг
     */
    public function rating(Request $request): JsonResponse
    {
        $types = [
            Post::$morphName,
            Article::$morphName,
            Photo::$morphName,
            Offer::$morphName,
            News::$morphName,
            Down::$morphName,
        ];

        $id = int($request->input('id'));
        $type = $request->input('type');
        $vote = $request->input('vote');

        if ($request->input('_token') !== csrf_token()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
            ]);
        }

        if (! in_array($vote, ['+', '-'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid rating',
            ]);
        }

        if (! in_array($type, $types, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Type invalid',
            ]);
        }

        /** @var BaseModel $model */
        $model = Relation::getMorphedModel($type);

        $post = $model::query()
            ->where('id', $id)
            ->where('user_id', '<>', getUser('id'))
            ->first();

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ]);
        }

        $polling = $post->polling()->first();
        $cancel = false;

        if ($polling) {
            if ($polling->vote === $vote) {
                return response()->json(['success' => false]);
            }

            $polling->delete();
            $cancel = true;
        } else {
            $post->polling()->create([
                'user_id'    => getUser('id'),
                'vote'       => $vote,
                'created_at' => SITETIME,
            ]);
        }

        if ($vote === '+') {
            $post->increment('rating');
        } else {
            $post->decrement('rating');
        }

        return response()->json([
            'success' => true,
            'cancel'  => $cancel,
            'rating'  => formatNum($post['rating'])->toHtml(),
        ]);
    }

    /**
     * Загружает файлы
     */
    public function uploadFile(Request $request, Validator $validator): JsonResponse
    {
        $imageTypes = [
            Article::$morphName,
            Item::$morphName,
            Photo::$morphName,
        ];

        $fileTypes = [
            Message::$morphName,
            Post::$morphName,
        ];

        $id = int($request->input('id'));
        $file = $request->file('file');
        $type = $request->input('type');

        if (! in_array($type, array_merge($imageTypes, $fileTypes), true)) {
            return response()->json([
                'success' => false,
                'message' => 'Type invalid',
            ]);
        }

        /** @var BaseModel $class */
        $class = Relation::getMorphedModel($type);
        $isImageType = in_array($type, $imageTypes, true);

        if ($id) {
            $model = $class::query()->find($id);

            if (! $model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found',
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

        if ($model->id) {
            $validator->true($model->user_id === getUser('id') || isAdmin(), __('ajax.record_not_author'));
        }

        if ($validator->isValid()) {
            $allowedExt = $isImageType ? setting('image_extensions') : setting('file_extensions');

            $rules = [
                'minweight'  => 100,
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', $allowedExt),
            ];

            $validator->file($file, $rules, __('validator.file_upload_failed'));
        }

        if ($validator->isValid()) {
            $fileData = $model->uploadFile($file);

            if ($isImageType) {
                $imageData = resizeProcess($fileData['path'], ['size' => 100]);
                $data = [
                    'success' => true,
                    'id'      => $fileData['id'],
                    'path'    => $imageData['path'],
                    'source'  => $imageData['source'],
                    'type'    => $fileData['type'],
                ];
            } else {
                $data = [
                    'success' => true,
                    'id'      => $fileData['id'],
                    'path'    => $fileData['path'],
                    'name'    => $fileData['name'],
                    'size'    => $fileData['size'],
                    'type'    => $fileData['type'],
                ];
            }

            return response()->json($data);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Удаляет файлы
     */
    public function deleteFile(Request $request, Validator $validator): JsonResponse
    {
        $types = [
            Article::$morphName,
            Item::$morphName,
            Photo::$morphName,
            Message::$morphName,
            Post::$morphName,
        ];

        $id = int($request->input('id'));
        $type = $request->input('type');

        if (! in_array($type, $types, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Type invalid',
            ]);
        }

        /** @var File $file */
        $file = File::query()
            ->where('relate_type', $type)
            ->find($id);

        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ]);
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($file->user_id === getUser('id') || isAdmin(), __('ajax.record_not_author'));

        if ($validator->isValid()) {
            $file->delete();

            return response()->json([
                'success' => true,
                'path'    => $file->hash,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Вставляет стикер
     */
    public function getStickers(): JsonResponse
    {
        $stickers = Sticker::query()
            //->where('category_id', $id)
            ->orderBy(DB::raw('CHAR_LENGTH(code)'))
            ->orderBy('name')
            ->get();

        $view = view('pages/_stickers_modal', compact('stickers'))->render();

        return response()->json([
            'success'  => true,
            'stickers' => $view,
        ]);
    }

    /**
     * Set theme
     */
    public function setTheme(Request $request): JsonResponse
    {
        cookie()->queue(
            cookie()->forever(
                'theme',
                $request->input('theme') === 'dark' ? 'dark' : 'light',
            )
        );

        return response()->json([
            'success' => true,
        ]);
    }
}
