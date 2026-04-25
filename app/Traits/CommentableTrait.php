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
use Illuminate\View\View;

trait CommentableTrait
{
    /**
     * Возвращает класс модели, с которой связан контроллер
     */
    abstract protected function commentableModel(): string;

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
     * Редирект на последнюю страницу комментариев после добавления
     */
    protected function redirectAfterCommentAdded(Model $model): RedirectResponse
    {
        $plural = Str::plural(Str::snake(class_basename($this->commentableModel())));
        $page = ceil($model->count_comments / setting('comments_per_page'));

        return redirect()->route($plural . '.comments', [
            'id'   => $model->id,
            'page' => $page > 1 ? $page : null,
        ]);
    }

    /**
     * Список комментариев и добавление нового
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        $model = $this->findCommentParent($id);

        $this->checkCommentableModel($model);

        $name = Str::snake(class_basename($this->commentableModel()));
        $plural = Str::plural($name);

        $cid = int($request->input('cid'));
        if ($cid) {
            $total = $model->comments()->where('id', '<=', $cid)->count();
            $page = ceil($total / setting('comments_per_page'));
            $page = $page > 1 ? $page : null;

            return redirect()->route($plural . '.comments', ['id' => $model->id, 'page' => $page])
                ->withFragment('comment_' . $cid);
        }

        $user = getUser();

        $files = $user
            ? File::query()
                ->where('relate_type', Comment::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->orderBy('created_at')
            : null;

        if ($request->isMethod('post')) {
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

                $files?->update(['relate_id' => $comment->id]);

                $user->increment('allcomments');
                $user->increment('point', setting('comment_point'));
                $user->increment('money', setting('comment_money'));

                $model->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, route($plural . '.comments', ['id' => $model->id, 'cid' => $comment->id], false), $model->title);

                setFlash('success', __('main.comment_added_success'));

                return $this->redirectAfterCommentAdded($model);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

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

        $files = $files?->get() ?? collect();

        return view($plural . '/comments', array_merge(
            [$name => $model],
            compact('comments', 'files')
        ));
    }

    /**
     * Редактирование комментария
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page', 1));

        $model = $this->findCommentParent($id);

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $comment = $model->comments()
            ->where('id', $cid)
            ->where('user_id', $user->id)
            ->first();

        if (! $comment) {
            abort(200, __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        $name = Str::snake(class_basename($this->commentableModel()));
        $plural = Str::plural($name);

        if ($request->isMethod('post')) {
            $page = int($request->input('page', 1));
            $msg = $request->input('msg');

            $validator->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')]);
            $validator->empty($model->closed, ['msg' => __('main.closed_comments')]);

            if ($validator->isValid()) {
                $comment->update(['text' => antimat($msg)]);

                setFlash('success', __('main.comment_edited_success'));

                return redirect()->route($plural . '.comments', ['id' => $model->id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view($plural . '/editcomment', array_merge(
            [$name => $model],
            compact('comment', 'page')
        ));
    }
}
