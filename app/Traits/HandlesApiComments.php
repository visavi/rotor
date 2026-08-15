<?php

declare(strict_types=1);

namespace App\Traits;

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
            ->with('user', 'files')
            ->orderBy('comments.created_at', $this->apiOrder($request))
            ->paginate($this->apiPerPage($request));
    }
}
