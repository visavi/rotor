<?php

declare(strict_types=1);

namespace App\Traits;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

/**
 * Комментарии записи для API разделов
 */
trait HandlesApiComments
{
    use HandlesApiPagination;

    /**
     * Комментарии записи страницей, с голосом текущего пользователя
     *
     * Дерево не строится: клиент собирает его сам по parent_id, иначе
     * пагинация резала бы ветки ответов
     *
     * @return LengthAwarePaginator<int, Comment>
     */
    protected function apiComments(Model $model, Request $request): LengthAwarePaginator
    {
        return Comment::query()
            // Удалённые нужны заглушкой, иначе ответы теряют родителя
            ->withoutGlobalScope('active')
            ->where('comments.relate_type', $model->getMorphClass())
            ->where('comments.relate_id', $model->getKey())
            ->select('comments.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('comments.id', 'polls.relate_id')
                    ->where('polls.relate_type', Comment::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            // Родитель нужен для контекста ответа, мягко удалённый — тоже
            ->with(['user', 'files', 'parent' => static function ($query) {
                $query->withoutGlobalScope('active')->with('user');
            }])
            ->orderBy('comments.created_at', $this->apiOrder($request))
            // Размер страницы берётся из настройки сайта, как и на страницах «Все комментарии»
            ->paginate($this->apiPerPage($request, (int) setting('comments_per_page')));
    }

    /**
     * Комментарии записи блоком со своей пагинацией
     *
     * Сама запись отдаётся в data, комментарии — вложенным ресурсом, поэтому
     * links и meta собираются вручную: Laravel добавляет их только верхнему уровню
     */
    protected function apiCommentsBlock(Model $model, Request $request): array
    {
        $comments = $this->apiComments($model, $request);

        return [
            'data'  => CommentResource::collection($comments->items()),
            'links' => [
                'first' => $comments->url(1),
                'last'  => $comments->url($comments->lastPage()),
                'prev'  => $comments->previousPageUrl(),
                'next'  => $comments->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $comments->currentPage(),
                'from'         => $comments->firstItem(),
                'last_page'    => $comments->lastPage(),
                'per_page'     => $comments->perPage(),
                'to'           => $comments->lastItem(),
                'total'        => $comments->total(),
            ],
        ];
    }
}
