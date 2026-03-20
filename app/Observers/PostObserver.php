<?php

declare(strict_types=1);

namespace App\Observers;

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

        clearCache(['statForums', 'recentTopics', 'TopicFeed']);
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $topic = $post->topic;

        $topic->decrement('count_posts');
        $topic->forum->decrement('count_posts');

        clearCache(['statForums', 'recentTopics', 'TopicFeed']);
    }
}
