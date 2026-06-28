<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Registry;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use App\Models\Message;
use App\Models\Poll;
use App\Models\Spam;
use App\Models\Sticker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
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
            case Message::$morphName:
                $model = Message::query()->find($id);
                break;

            case Comment::$morphName:
                $model = Comment::query()->find($id);
                $path = $model?->getViewUrl(false);
                break;

            default:
                if (isset(Registry::$complaintTypes[$type])) {
                    $result = (Registry::$complaintTypes[$type])($id, $page);
                    $model = $result['model'] ?? null;
                    $path = $result['path'] ?? null;
                }
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
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Связь голоса текущего пользователя (morph-имя relate единое по движку)
     *
     * @return MorphOne<Poll, Model>
     */
    private function pollRelation(Model $post): MorphOne
    {
        return $post->morphOne(Poll::class, 'relate')
            ->where('user_id', getUser('id'));
    }

    /**
     * Изменяет рейтинг
     */
    public function rating(Request $request): JsonResponse
    {
        $validTypes = array_merge([
            Comment::$morphName,
        ], Registry::$ratingTypes);

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

        $poll = $this->pollRelation($post)->firstOrNew();
        $isCancel = false;

        if ($poll->exists) {
            if ($poll->vote === $vote) {
                return response()->json(['success' => false]);
            }
            $isCancel = true;
            $poll->delete();
        }

        if (! $isCancel) {
            $this->pollRelation($post)->create([
                'user_id' => getUser('id'),
                'vote'    => $vote,
            ]);
        }

        $vote === '+' ? $post->increment('rating') : $post->decrement('rating');
        $post->refresh();

        return response()->json([
            'success' => true,
            'cancel'  => $isCancel,
            'rating'  => formatNum((int) $post->getAttribute('rating'))->toHtml(),
        ]);
    }

    /**
     * Загружает файлы
     */
    public function uploadFile(Request $request, Validator $validator): JsonResponse
    {
        $imageTypes = Registry::$mediaTypes;

        $fileTypes = array_merge([
            Comment::$morphName,
            Message::$morphName,
        ], Registry::$fileTypes);

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

        $uploadedFiles = File::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUser('id'))
            ->get(['name']);

        $duplicate = $file && $uploadedFiles->contains(
            'name',
            Str::substr(getBodyName($file->getClientOriginalName()), 0, 50) . '.' . strtolower($file->getClientOriginalExtension())
        );

        $validator
            ->lt($uploadedFiles->count(), setting('maxfiles'), __('validator.files_max', ['max' => setting('maxfiles')]))
            ->false($duplicate, __('validator.file_duplicate'));

        if ($model->id) {
            $validator->true($model->user_id === getUser('id') || isAdmin(), __('ajax.record_not_author'));
        }

        if ($validator->isValid()) {
            $allowedExt = setting($isImageType ? 'media_extensions' : 'file_extensions');

            $rules = [
                'minweight'  => 100,
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', $allowedExt),
            ];

            $validator->file($file, $rules, __('validator.file_upload_failed'));
        }

        if ($validator->isValid()) {
            $fileData = $model->uploadFile($file);
            if (method_exists($model, 'convertVideo')) {
                $model->convertVideo($fileData);
            }

            if ($isImageType) {
                $data = [
                    'success' => true,
                    'id'      => $fileData['id'],
                    'path'    => $fileData['path'],
                    'type'    => $fileData['type'],
                ];
            } else {
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
        $types = array_merge([
            Comment::$morphName,
            Message::$morphName,
        ], Registry::$mediaTypes, Registry::$fileTypes);

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
            ->toBase()
            ->map(fn ($items, $categoryId) => [
                'id'       => (int) $categoryId,
                'name'     => $items->first()->category->name,
                'stickers' => $items->map(fn (Sticker $s) => ['code' => $s->code, 'name' => $s->name])->values()->all(),
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
