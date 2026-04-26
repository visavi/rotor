<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
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

        $files = $comment->files->map(fn (File $file) => [
            'id'      => $file->id,
            'path'    => $file->path,
            'name'    => $file->name,
            'size'    => formatSize($file->size),
            'isImage' => $file->isImage(),
            'type'    => Comment::$morphName,
        ]);

        return response()->json(['files' => $files, 'text' => $comment->text]);
    }

    /**
     * Редактирует комментарий
     */
    public function update(int $id, Request $request, Validator $validator): JsonResponse
    {
        if (! $user = getUser()) {
            return response()->json(['success' => false, 'message' => __('main.not_authorized')]);
        }

        $msg = $request->input('msg');

        $comment = Comment::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $comment) {
            return response()->json(['success' => false, 'message' => __('main.comment_deleted')]);
        }

        if ($comment->created_at + 600 < SITETIME) {
            return response()->json(['success' => false, 'message' => __('main.editing_impossible')]);
        }

        $validator->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')]);

        $relate = $comment->relate;
        if ($relate && array_key_exists('closed', $relate->getAttributes()) && $relate->closed) {
            return response()->json(['success' => false, 'message' => __('main.closed_comments')]);
        }

        if (! $validator->isValid()) {
            return response()->json(['success' => false, 'message' => current($validator->getErrors())]);
        }

        $comment->update(['text' => antimat($msg)]);

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

        $relateType = $comment->relate_type;
        $relateId = $comment->relate_id;

        $comment->delete();

        $class = Relation::getMorphedModel($relateType);
        $model = $class::query()->find($relateId);
        if ($model) {
            $model->decrement('count_comments');
        }

        return response()->json(['success' => true]);
    }
}
