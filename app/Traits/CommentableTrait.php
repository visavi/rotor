<?php

declare(strict_types=1);

namespace App\Traits;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait CommentableTrait
{
    /**
     * Возвращает класс модели, с которой связан контроллер
     */
    protected function commentableModel(): string
    {
        return 'App\\Models\\' . Str::before(class_basename(static::class), 'Controller');
    }

    /**
     * Возвращает [routeName, params] для редиректа на страницу модели
     */
    protected function commentableViewRoute(Model $model): array
    {
        $plural = Str::plural(Str::snake(class_basename($this->commentableModel())));

        return [$plural . '.view', ['id' => $model->id]];
    }

    /**
     * Находит родительскую модель по id или прерывает с 404
     */
    protected function findCommentParent(int $id): Model
    {
        $model = ($this->commentableModel())::query()->find($id);
        if (! $model) {
            abort(404, __('main.record_not_found'));
        }

        return $model;
    }

    /**
     * Проверяет доступность модели (прерывает если active = false)
     */
    protected function checkCommentableModel(Model $model): void
    {
        if (array_key_exists('active', $model->getAttributes()) && ! $model->active) {
            abort(200, __('main.record_not_active'));
        }
    }

    /**
     * Возвращает комментарии в виде дерева и файлы для передачи во вью
     */
    protected function getCommentsData(Model $model): array
    {
        $user = getUser();

        $files = $user
            ? File::query()
                ->where('relate_type', Comment::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->orderBy('created_at')
                ->get()
            : collect();

        $allComments = $model->comments()
            ->select('comments.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('comments.id', 'polls.relate_id')
                    ->where('polls.relate_type', Comment::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->orderBy('created_at')
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
     */
    public function storeComment(int $id, Request $request, Validator $validator, Flood $flood): RedirectResponse|JsonResponse
    {
        $model = $this->findCommentParent($id);

        [$viewRoute, $viewParams] = $this->commentableViewRoute($model);

        $user = getUser();
        $msg = $request->input('msg');

        $validator
            ->true($user, __('main.not_authorized'))
            ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
            ->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')]);

        $validator->empty($model->closed, ['msg' => __('main.closed_comments')]);

        if ($validator->isValid()) {
            $msg = antimat($msg);

            $parentId = null;
            $depth = 0;
            $parentComment = $request->input('parent_id')
                ? Comment::query()->find((int) $request->input('parent_id'))
                : null;

            if ($parentComment && $parentComment->relate_id === $model->id) {
                if ($parentComment->depth >= setting('comment_depth')) {
                    $parentId = $parentComment->parent_id;
                    $depth = $parentComment->depth;
                } else {
                    $parentId = $parentComment->id;
                    $depth = $parentComment->depth + 1;
                }
            }

            $comment = $model->comments()->create([
                'text'       => $msg,
                'user_id'    => $user->id,
                'parent_id'  => $parentId,
                'depth'      => $depth,
                'created_at' => SITETIME,
                'ip'         => getIp(),
                'brow'       => getBrowser(),
            ]);

            File::query()
                ->where('relate_type', Comment::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->update(['relate_id' => $comment->id]);

            $user->increment('allcomments');
            $user->increment('point', setting('comment_point'));
            $user->increment('money', setting('comment_money'));

            $model->increment('count_comments');

            $flood->saveState();

            $replyUser = $parentComment?->user?->exists ? $parentComment->user : null;
            sendNotify($msg, route($viewRoute, $viewParams, false) . '#comment_' . $comment->id, $model->title, $replyUser);

            if ($request->wantsJson()) {
                return response()->json(['redirect' => route($viewRoute, $viewParams) . '#comment_' . $comment->id]);
            }

            setFlash('success', __('main.comment_added_success'));

            return $this->redirectAfterCommentAdded($model, $comment->id);
        }

        if ($request->wantsJson()) {
            return response()->json(['errors' => $validator->getErrors()], 422);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect()->route($viewRoute, $viewParams);
    }
}
