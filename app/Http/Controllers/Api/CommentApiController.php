<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Flood;
use App\Services\CommentService;
use App\Services\FileService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentApiController extends Controller
{
    public function __construct(private readonly CommentService $comments)
    {
    }

    /**
     * Комментарий по id
     */
    public function show(int $id): JsonResource
    {
        $comment = Comment::query()
            ->withoutGlobalScope('active')
            ->with(['user', 'files', 'parent' => static function ($query) {
                $query->withoutGlobalScope('active')->with('user');
            }])
            ->find($id);

        if (! $comment) {
            abort(404, __('main.comment_deleted'));
        }

        return CommentResource::make($comment);
    }

    /**
     * Добавление комментария к записи любого раздела
     */
    public function store(Request $request, Flood $flood): JsonResponse
    {
        $user = getUser();

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', CommentService::types())],
            'id'   => ['required', 'integer', 'min:1'],
            'text' => [
                'required',
                'string',
                'min:' . setting('comment_text_min'),
                'max:' . setting('comment_text_max'),
                static function (string $attribute, mixed $value, Closure $fail) use ($flood) {
                    if ($flood->isFlood()) {
                        $fail(__('validator.flood', ['sec' => $flood->getPeriod()]));
                    }
                },
            ],
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'files'     => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*'   => ['file', 'max:' . FileService::maxFileSize(), 'mimes:' . setting('file_extensions')],
        ]);

        $model = $this->findRecord($validated['type'], (int) $validated['id']);

        if ($model->getAttribute('closed')) {
            abort(422, __('main.closed_comments'));
        }

        // Запись, скрытую с сайта, комментировать нельзя
        if (array_key_exists('active', $model->getAttributes()) && ! $model->getAttribute('active')) {
            abort(422, __('main.record_not_active'));
        }

        $comment = $this->comments->create(
            $model,
            $user,
            antimat($validated['text']),
            isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
        );

        foreach ($request->file('files', []) as $file) {
            $comment->uploadFile($file);
        }

        $flood->saveState();

        $comment->load(['user', 'files', 'parent' => static function ($query) {
            $query->withoutGlobalScope('active')->with('user');
        }]);

        return response()->json([
            'message' => __('main.comment_added_success'),
            'comment' => CommentResource::make($comment),
        ], 201);
    }

    /**
     * Редактирование своего комментария
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $user = getUser();

        $comment = Comment::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $comment) {
            abort(404, __('main.comment_deleted'));
        }

        // Правка доступна ограниченное время после публикации
        if (! $this->comments->editable($comment)) {
            abort(422, __('main.editing_impossible'));
        }

        if ($comment->relate?->getAttribute('closed')) {
            abort(422, __('main.closed_comments'));
        }

        $validated = $request->validate([
            'text' => [
                'required',
                'string',
                'min:' . setting('comment_text_min'),
                'max:' . setting('comment_text_max'),
            ],
        ]);

        $this->comments->update($comment, antimat($validated['text']));

        $comment->load(['user', 'files', 'parent' => static function ($query) {
            $query->withoutGlobalScope('active')->with('user');
        }]);

        return response()->json([
            'message' => __('main.message_edited_success'),
            'comment' => CommentResource::make($comment),
        ]);
    }

    /**
     * Удаление комментария, как и на сайте — только администрацией
     */
    public function destroy(int $id): JsonResponse
    {
        if (! isAdmin()) {
            abort(403, __('main.not_authorized'));
        }

        $comment = Comment::query()->find($id);

        if (! $comment) {
            abort(404, __('main.comment_deleted'));
        }

        $softDeleted = $this->comments->delete($comment);

        return response()->json([
            'message'      => __('main.record_deleted_success'),
            'soft_deleted' => $softDeleted,
        ]);
    }

    /**
     * Находит комментируемую запись по типу и id
     */
    private function findRecord(string $type, int $id): Model
    {
        $class = Relation::getMorphedModel($type);
        $model = $class ? $class::query()->find($id) : null;

        if (! $model) {
            abort(404, __('main.record_not_found'));
        }

        return $model;
    }
}
