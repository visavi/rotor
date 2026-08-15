<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Message;
use App\Models\Spam;
use App\Models\Sticker;
use App\Services\FileService;
use App\Services\RatingService;
use App\Support\Registry;
use App\Support\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Изменяет рейтинг
     */
    public function rating(Request $request, RatingService $rating): JsonResponse
    {
        $result = $rating->vote(
            getUser(),
            $request->input('type'),
            int($request->input('id')),
            $request->input('vote'),
        );

        if (isset($result['rating'])) {
            $result['rating'] = formatNum($result['rating'])->toHtml();
        }

        return response()->json($result);
    }

    /**
     * Загружает файлы
     */
    public function uploadFile(Request $request, Validator $validator, FileService $uploader): JsonResponse
    {
        $result = $uploader->upload(
            $request->file('file'),
            (string) $request->input('type'),
            int($request->input('id')),
            $validator,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']]);
        }

        $fileData = $result['data'];
        $isImage = $fileData['type'] === 'image';

        // Галерее хватает пути, списку файлов нужны имя и размер
        $data = $isImage
            ? [
                'success' => true,
                'id'      => $fileData['id'],
                'path'    => $fileData['path'],
                'type'    => $fileData['type'],
            ]
            : [
                'success' => true,
                'id'      => $fileData['id'],
                'path'    => $fileData['path'],
                'name'    => $fileData['name'],
                'size'    => $fileData['size'],
                'type'    => $fileData['type'],
            ];

        return response()->json($data);
    }

    /**
     * Удаляет файлы
     */
    public function deleteFile(Request $request, Validator $validator, FileService $uploader): JsonResponse
    {
        $result = $uploader->remove(
            int($request->input('id')),
            (string) $request->input('type'),
            $validator,
        );

        return response()->json($result);
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
