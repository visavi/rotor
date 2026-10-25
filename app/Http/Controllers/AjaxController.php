<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sticker;
use App\Models\User;
use App\Services\ComplaintService;
use App\Services\FileService;
use App\Services\OgImageService;
use App\Services\RatingService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AjaxController extends Controller
{
    /**
     * Отправляет жалобу на сообщение
     */
    public function complaint(Request $request, ComplaintService $complaint): JsonResponse
    {
        $type = $request->input('type');

        $result = $complaint->create(
            is_string($type) ? $type : '',
            $request->integer('id'),
            $request->input('page'),
        );

        return $this->serviceResponse($result);
    }

    /**
     * Изменяет рейтинг
     */
    public function rating(Request $request, RatingService $rating, #[CurrentUser] User $user): JsonResponse
    {
        $result = $rating->vote(
            $user,
            $request->input('type'),
            $request->integer('id'),
            $request->input('vote'),
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], $result['status']);
        }

        // Блок рейтинга перерисовывает сервер: стрелки и значение приходят готовыми
        return response()->json([
            'success' => true,
            'html'    => (string) view('app/_rating', [
                'model' => $result['post'],
                'vote'  => $result['vote'],
            ]),
        ]);
    }

    /**
     * Загружает файлы
     */
    public function uploadFile(Request $request, FileService $uploader): JsonResponse
    {
        $type = $request->input('type');
        $file = $request->file('file');

        $result = $uploader->upload(
            $file instanceof UploadedFile ? $file : null,
            is_string($type) ? $type : '',
            $request->integer('id'),
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], $result['status']);
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
    public function deleteFile(Request $request, FileService $uploader): JsonResponse
    {
        $type = $request->input('type');

        $result = $uploader->remove(
            $request->integer('id'),
            is_string($type) ? $type : '',
        );

        return $this->serviceResponse($result);
    }

    /**
     * Сохраняет порядок файлов: sort — id через запятую, как их пишет перетаскивание
     */
    public function sortFiles(Request $request, FileService $uploader): JsonResponse
    {
        $sort = $request->input('sort');
        $type = $request->input('type');

        $result = $uploader->sort(
            array_map(intval(...), explode(',', is_string($sort) ? $sort : '')),
            is_string($type) ? $type : '',
        );

        return $this->serviceResponse($result);
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
                'name'     => $items->firstOrFail()->category->name,
                'stickers' => $items->map(fn (Sticker $s) => ['code' => $s->code, 'name' => $s->name])->values()->all(),
            ])
            ->values();

        return response()->json($grouped);
    }

    /**
     * Резолв прямой ссылки на картинку через og:image
     */
    public function resolveImage(Request $request, OgImageService $ogImage): JsonResponse
    {
        $url = $request->input('url');
        $url = is_string($url) ? filter_var($url, FILTER_VALIDATE_URL) : false;

        // Схему, порт и адрес проверяет сервис: file:// и php:// читали бы файлы сервера
        return response()->json(['image' => $url ? $ogImage->find($url) : null]);
    }

    /**
     * Set sidebar state
     */
    public function setSidebar(Request $request): JsonResponse
    {
        cookie()->queue(
            cookie()->forever(
                'sidebar',
                $request->input('sidebar') === 'mini' ? 'mini' : 'full',
            )
        );

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Подсказки города и страны для анкеты: что уже вписали другие, частое первым
     */
    public function places(string $field, Request $request): JsonResponse
    {
        $query = $request->string('query')->trim()->value();

        if (Str::length($query) < 2) {
            return response()->json([]);
        }

        $places = array_map('strval', array_keys(User::placeCounts($field, $query)));

        return response()->json(array_map(static fn (string $place) => ['value' => $place, 'label' => $place], $places));
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

    /**
     * Ответ сервиса как есть, отказ — с его кодом; сам status в тело не попадает
     *
     * @param array{success: true}|array{success: false, status: int} $result
     */
    private function serviceResponse(array $result): JsonResponse
    {
        return response()->json(Arr::except($result, 'status'), $result['success'] ? 200 : $result['status']);
    }
}
