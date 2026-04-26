<?php

declare(strict_types=1);

namespace App\Traits;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait CommentableTrait
{
    /**
     * Возвращает класс модели, с которой связан контроллер
     */
    abstract protected function commentableModel(): string;

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
     * Редирект к конкретному комментарию (по cid), или null если cid не передан
     */
    protected function cidRedirect(Model $model, Request $request): ?RedirectResponse
    {
        $cid = int($request->input('cid'));
        if (! $cid) {
            return null;
        }

        [$route, $params] = $this->commentableViewRoute($model);
        $total = $model->comments()->where('id', '<=', $cid)->count();
        $page = ceil($total / setting('comments_per_page'));

        return redirect()
            ->route($route, array_merge($params, ['page' => $page > 1 ? $page : null]))
            ->withFragment('comment_' . $cid);
    }

    /**
     * Возвращает пагинированные комментарии и файлы для передачи во вью
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

        $comments = $model->comments()
            ->select('comments.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('comments.id', 'polls.relate_id')
                    ->where('polls.relate_type', Comment::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->orderBy('created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return compact('comments', 'files');
    }

    /**
     * Редирект на последнюю страницу после добавления комментария
     */
    protected function redirectAfterCommentAdded(Model $model): RedirectResponse
    {
        [$route, $params] = $this->commentableViewRoute($model);
        $page = ceil($model->count_comments / setting('comments_per_page'));

        return redirect()
            ->route($route, array_merge($params, ['page' => $page > 1 ? $page : null]))
            ->withFragment('comments');
    }

    /**
     * Добавление комментария (POST-обработчик)
     */
    public function storeComment(int $id, Request $request, Validator $validator, Flood $flood): RedirectResponse
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

            $comment = $model->comments()->create([
                'text'       => $msg,
                'user_id'    => $user->id,
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
            sendNotify($msg, route($viewRoute, array_merge($viewParams, ['cid' => $comment->id]), false), $model->title);

            setFlash('success', __('main.comment_added_success'));

            return $this->redirectAfterCommentAdded($model);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect()->route($viewRoute, $viewParams);
    }
}
