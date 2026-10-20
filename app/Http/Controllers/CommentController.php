<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\File;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $comments)
    {
    }

    /**
     * Возвращает данные комментария для редактирования (текст + файлы)
     */
    public function show(int $id): JsonResponse
    {
        $comment = Comment::query()
            ->where('id', $id)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            return response()->json(['files' => [], 'text' => '']);
        }

        // Форма ответа та же, что у /ajax/file/upload: модалка рисует оба одним кодом
        $files = $comment->files->map(fn (File $file) => [
            'id'   => $file->id,
            'path' => $file->path,
            'name' => $file->name,
            'size' => formatSize($file->size),
            'type' => match (true) {
                $file->isImage() => 'image',
                $file->isVideo() => 'video',
                default          => 'file',
            },
        ]);

        return response()->json(['files' => $files, 'text' => $comment->text]);
    }

    /**
     * Редактирует комментарий
     */
    public function update(int $id, Request $request): JsonResponse
    {
        if (! $user = getUser()) {
            return response()->json(['success' => false, 'message' => __('main.not_authorized')]);
        }

        $comment = Comment::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $comment) {
            return response()->json(['success' => false, 'message' => __('main.comment_deleted')]);
        }

        if (! $this->comments->editable($comment)) {
            return response()->json(['success' => false, 'message' => __('main.editing_impossible')]);
        }

        if ($comment->relate?->getAttribute('closed')) {
            return response()->json(['success' => false, 'message' => __('main.closed_comments')]);
        }

        // Ajax ждёт {success, message}, а не 422 с errors — проверка без FormRequest
        $validator = Validator::make($request->only('msg'), [
            'msg' => CommentRequest::textRules(),
        ], [
            'msg.required' => __('validator.text'),
            'msg.string'   => __('validator.text'),
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $this->comments->update($comment, $validator->validated()['msg']);

        return response()->json([
            'success' => true,
            'text'    => $comment->getText()->toHtml(),
        ]);
    }

    /**
     * Удаляет комментарий
     */
    public function destroy(int $id): JsonResponse
    {
        if (! isAdmin()) {
            return response()->json(['success' => false, 'message' => __('main.not_authorized')]);
        }

        $comment = Comment::query()->find($id);

        if (! $comment) {
            return response()->json(['success' => false, 'message' => __('main.comment_deleted')]);
        }

        $softDeleted = $this->comments->delete($comment);

        return response()->json([
            'success'      => true,
            'soft_deleted' => $softDeleted,
            'message'      => __('main.record_deleted_success'),
            // Место комментария занимает заглушка там, где список её показывает
            'html' => (string) view('app/_comment_removed'),
        ]);
    }
}
