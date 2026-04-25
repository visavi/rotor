<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Article;
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
use Illuminate\Support\Str;

class AjaxController extends Controller
{
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

        switch ($type) {
            case Guestbook::$morphName:
                $model = Guestbook::query()->find($id);
                $path = route('guestbook.index', ['page' => $page], false);
                break;

            case Post::$morphName:
                $model = Post::query()->find($id);
                $path = route('topics.topic', ['id' => $model->topic_id, 'pid' => $model->id], false);
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
                $path = route($model->relate_type . '.comments', ['id' => $model->relate_id, 'cid' => $model->id], false);
                $type = 'comments';
                break;
        }

        $spam = Spam::query()->where(['relate_type' => $type, 'relate_id' => $id])->first();

        $validator
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

        if ($validator->isValid()) {
            $delComments = Comment::query()
                ->where('relate_type', $type)
                ->where('relate_id', $rid)
                ->where('id', $id)
                ->delete();

            if ($delComments) {
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
        $validTypes = [
            Post::$morphName,
            Article::$morphName,
            Photo::$morphName,
            Offer::$morphName,
            News::$morphName,
            Down::$morphName,
            Comment::$morphName,
        ];

        $type = $request->input('type');
        $vote = $request->input('vote');

        if (! in_array($type, $validTypes, true)) {
            return response()->json(['success' => false, 'message' => 'Type invalid']);
        }

        if (! in_array($vote, ['+', '-'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid rating']);
        }

        $model = Relation::getMorphedModel($type);
        $post = $model::query()
            ->where('id', int($request->input('id')))
            ->where('user_id', '<>', getUser('id'))
            ->first();

        if (! $post) {
            return response()->json(['success' => false, 'message' => __('main.record_not_found')]);
        }

        $poll = $post->poll()->firstOrNew();
        $isCancel = false;

        if ($poll->exists) {
            if ($poll->vote === $vote) {
                return response()->json(['success' => false]);
            }
            $isCancel = true;
            $poll->delete();
        }

        if (! $isCancel) {
            $post->poll()->create([
                'user_id'    => getUser('id'),
                'vote'       => $vote,
                'created_at' => SITETIME,
            ]);
        }

        $vote === '+' ? $post->increment('rating') : $post->decrement('rating');
        $post->refresh();

        return response()->json([
            'success' => true,
            'cancel'  => $isCancel,
            'rating'  => formatNum($post->rating)->toHtml(),
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
            Comment::$morphName,
            Down::$morphName,
            Message::$morphName,
            News::$morphName,
            Post::$morphName,
            Guestbook::$morphName,
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

        $duplicate = $file && File::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUser('id'))
            ->where('name', Str::substr(getBodyName($file->getClientOriginalName()), 0, 50) . '.' . strtolower($file->getClientOriginalExtension()))
            ->exists();

        $validator
            ->lt($countFiles, setting('maxfiles'), __('validator.files_max', ['max' => setting('maxfiles')]))
            ->false($duplicate, __('validator.file_duplicate'));

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
                $data = [
                    'success' => true,
                    'id'      => $fileData['id'],
                    'path'    => $fileData['path'],
                    'source'  => $fileData['path'],
                    'type'    => $fileData['type'],
                ];
            } else {
                if (method_exists($model, 'convertVideo')) {
                    $model->convertVideo($fileData);
                }

                if (method_exists($model, 'addFileToArchive')) {
                    $model->addFileToArchive($fileData);
                }

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
            Comment::$morphName,
            Down::$morphName,
            Item::$morphName,
            News::$morphName,
            Message::$morphName,
            Photo::$morphName,
            Post::$morphName,
            Guestbook::$morphName,
        ];

        $id = int($request->input('id'));
        $type = $request->input('type');

        if (! in_array($type, $types, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Type invalid',
            ]);
        }

        $file = File::query()
            ->where('relate_type', $type)
            ->find($id);

        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ]);
        }

        $validator
            ->true($file->user_id === getUser('id') || isAdmin(), __('ajax.record_not_author'));

        if ($validator->isValid()) {
            $file->delete();

            return response()->json([
                'success' => true,
                'path'    => $file->path,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Возвращает список стикеров
     */
    public function getStickers(): JsonResponse
    {
        $stickers = Sticker::query()
            ->with('category:id,name')
            ->orderBy(DB::raw('CHAR_LENGTH(code)'))
            ->orderBy('name')
            ->get(['id', 'category_id', 'code', 'name']);

        $grouped = $stickers
            ->groupBy('category_id')
            ->map(fn ($items, $categoryId) => [
                'id'       => $categoryId,
                'name'     => $items->first()->category->name,
                'stickers' => $items->map(fn ($s) => ['code' => $s->code, 'name' => $s->name])->values(),
            ])
            ->values();

        return response()->json($grouped);
    }

    /**
     * Резолв прямой ссылки на картинку через og:image
     */
    public function resolveImage(Request $request): JsonResponse
    {
        $url = filter_var((string) $request->input('url'), FILTER_VALIDATE_URL);

        if (! $url) {
            return response()->json(['image' => null]);
        }

        $ctx = stream_context_create(['http' => [
            'timeout'         => 5,
            'follow_location' => true,
            'user_agent'      => 'Mozilla/5.0',
        ]]);

        $html = @file_get_contents($url, false, $ctx);

        if ($html && preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](https?:[^"\']+)["\']/i', $html, $m)) {
            return response()->json(['image' => $m[1]]);
        }

        return response()->json(['image' => null]);
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
