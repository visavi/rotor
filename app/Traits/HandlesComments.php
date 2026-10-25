<?php

declare(strict_types=1);

namespace App\Traits;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use App\Services\CommentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait HandlesComments
{
    /**
     * Связь комментариев модели (morph-имя relate единое по движку)
     *
     * @return MorphMany<Comment, Model>
     */
    private function commentsRelation(Model $model): MorphMany
    {
        return $model->morphMany(Comment::class, 'relate')->with('relate');
    }

    /**
     * Возвращает [routeName, params] для редиректа на страницу модели
     */
    protected function commentableViewRoute(Model $model): array
    {
        $plural = Str::plural(Str::snake(class_basename($this->commentableModelClass)));

        return [$plural . '.view', ['id' => $model->getKey()]];
    }

    /**
     * Находит родительскую модель по id или прерывает с 404
     */
    protected function findCommentParent(int $id): Model
    {
        $model = ($this->commentableModelClass)::query()->findOrFail($id);

        return $model;
    }

    /**
     * Проверяет доступность модели (прерывает если active = false)
     */
    protected function checkCommentableModel(Model $model): void
    {
        if (array_key_exists('active', $model->getAttributes()) && ! $model->getAttribute('active')) {
            abort(403, __('main.record_not_active'));
        }
    }

    /**
     * Возвращает комментарии в виде дерева и файлы для передачи во вью
     */
    protected function getCommentsData(Model $model): array
    {
        $user = auth()->user();

        $files = $user
            ? File::query()
                ->pending(Comment::$morphName, $user->id)
                ->ordered()
                ->get()
            : collect();

        $allComments = $this->commentsRelation($model)
            ->withoutGlobalScope('active')
            ->withUserVote()
            ->orderBy('comments.created_at')
            ->with(['user', 'files'])
            ->get();

        $comments = $this->buildCommentTree($allComments);

        return compact('comments', 'files');
    }

    /**
     * Строит дерево комментариев из плоской коллекции
     */
    protected function buildCommentTree(Collection $all, ?int $parentId = null): Collection
    {
        return $all
            ->where('parent_id', $parentId)
            ->map(function (Comment $comment) use ($all) {
                $comment->setRelation('children', $this->buildCommentTree($all, $comment->id));

                return $comment;
            })
            ->values();
    }

    /**
     * Редирект на страницу с якорем на добавленный комментарий
     */
    protected function redirectAfterCommentAdded(Model $model, int $commentId): RedirectResponse
    {
        [$route, $params] = $this->commentableViewRoute($model);

        return redirect()
            ->route($route, $params)
            ->withFragment('comment_' . $commentId);
    }

    /**
     * Добавление комментария (POST-обработчик)
     *
     * Форма ответа шлёт запрос с Accept: application/json и ждёт адрес
     * записи или 422 со списком ошибок, обычная форма — редирект
     */
    public function storeComment(int $id, CommentRequest $request, Flood $flood, CommentService $commentService): RedirectResponse|JsonResponse
    {
        $model = $this->findCommentParent($id);

        $this->checkCommentableModel($model);

        if ($model->getAttribute('closed')) {
            throw ValidationException::withMessages(['msg' => __('main.closed_comments')]);
        }

        [$viewRoute, $viewParams] = $this->commentableViewRoute($model);

        $comment = $commentService->create(
            $model,
            auth()->user() ?? abort(403),
            $request->validated('msg'),
            $request->validated('parent_id') ? (int) $request->validated('parent_id') : null,
            route($viewRoute, $viewParams, false),
        );

        $flood->saveState();

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route($viewRoute, $viewParams) . '#comment_' . $comment->id]);
        }

        return $this->redirectAfterCommentAdded($model, $comment->id)
            ->with('success', __('main.comment_added_success'));
    }
}
