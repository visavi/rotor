<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Classes\FileUploader;
use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileApiController extends Controller
{
    public function __construct(private readonly FileUploader $uploader)
    {
    }

    /**
     * Свои вложения по типу, по умолчанию — еще не привязанные к записи
     */
    public function index(Request $request): JsonResource
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', FileUploader::types())],
            'id'   => ['nullable', 'integer', 'min:0'],
        ]);

        $files = File::query()
            ->where('relate_type', $validated['type'])
            ->where('relate_id', (int) ($validated['id'] ?? 0))
            ->where('user_id', getUser('id'))
            ->orderBy('created_at')
            ->get();

        return FileResource::collection($files);
    }

    /**
     * Загрузка вложения, id = 0 — запись еще не создана,
     * такие файлы привяжутся к ней при сохранении
     */
    public function store(Request $request, Validator $validator): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', FileUploader::types())],
            'id'   => ['nullable', 'integer', 'min:0'],
            'file' => ['required', 'file'],
        ]);

        $result = $this->uploader->upload(
            $request->file('file'),
            (string) $request->input('type'),
            $request->integer('id'),
            $validator,
        );

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => __('main.file_uploaded_success'),
            'file'    => FileResource::make($result['file']),
        ], 201);
    }

    /**
     * Удаление своего вложения
     */
    public function destroy(int $id, Request $request, Validator $validator): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', FileUploader::types())],
        ]);

        $result = $this->uploader->remove($id, (string) $request->input('type'), $validator);

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => __('main.file_deleted_success')]);
    }
}
