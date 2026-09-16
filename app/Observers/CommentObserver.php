<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Comment;
use App\Models\Feed;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        Feed::query()
            ->where('relate_type', Comment::$morphName)
            ->whereIn('relate_id', function ($query) use ($comment) {
                $query->select('id')
                    ->from('comments')
                    ->where('relate_type', $comment->relate_type)
                    ->where('relate_id', $comment->relate_id);
            })
            ->delete();

        Feed::query()->insert([
            'relate_type' => Comment::$morphName,
            'relate_id'   => $comment->id,
            'created_at'  => $comment->created_at,
        ]);

        cache()->increment('feed_version');
    }

    /**
     * Лента кеширует сами модели, поэтому правка текста иначе висит до конца TTL.
     * Служебные апдейты (рейтинг, модерация) кеш не роняют, как и правки при
     * выключенных в ленте комментариях: строка в feeds пишется независимо от настройки
     */
    public function updated(Comment $comment): void
    {
        if ($comment->wasChanged('text') && setting('feed_comments_show')) {
            cache()->increment('feed_version');
        }
    }

    public function deleted(Comment $comment): void
    {
        Feed::query()
            ->where('relate_type', Comment::$morphName)
            ->where('relate_id', $comment->id)
            ->delete();

        cache()->increment('feed_version');
    }
}
