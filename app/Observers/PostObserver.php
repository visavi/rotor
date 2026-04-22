<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Feed;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $topic = $post->topic;
        $forum = $topic->forum;

        $post->user->increment('allforum');
        $post->user->increment('point', setting('forum_point'));
        $post->user->increment('money', setting('forum_money'));

        $topic->increment('count_posts');
        $topic->update([
            'last_post_id' => $post->id,
            'updated_at'   => SITETIME,
        ]);

        $forum->update([
            'count_posts'   => DB::raw('count_posts + 1'),
            'last_topic_id' => $topic->id,
        ]);

        if ($forum->parent_id) {
            $forum->parent->update([
                'last_topic_id' => $topic->id,
            ]);
        }

        Feed::query()->updateOrInsert(
            ['relate_type' => 'topics', 'relate_id' => $post->topic_id],
            ['created_at' => $post->created_at]
        );

        cache()->increment('feed_version');
        clearCache(['statForums', 'recentTopics']);
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $topic = $post->topic;

        $topic->decrement('count_posts');
        $topic->forum->decrement('count_posts');

        // Обновляем created_at в feeds на время нового последнего поста
        $freshTopic = $topic->fresh();
        if ($freshTopic->last_post_id) {
            Feed::query()->updateOrInsert(
                ['relate_type' => 'topics', 'relate_id' => $topic->id],
                ['created_at' => $freshTopic->lastPost->created_at]
            );
        } else {
            Feed::query()->where('relate_type', 'topics')->where('relate_id', $topic->id)->delete();
        }

        cache()->increment('feed_version');

        clearCache(['statForums', 'recentTopics']);
    }
}
