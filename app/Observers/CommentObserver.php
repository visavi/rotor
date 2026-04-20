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

    public function deleted(Comment $comment): void
    {
        Feed::query()
            ->where('relate_type', Comment::$morphName)
            ->where('relate_id', $comment->id)
            ->delete();

        cache()->increment('feed_version');
    }
}
