<?php

declare(strict_types=1);

namespace Modules\Forum\Observers;

use Modules\Forum\Models\Topic;

class TopicObserver
{
    /**
     * Handle the Topic "created" event.
     */
    public function created(Topic $topic): void
    {
        $topic->forum->increment('count_topics');
    }

    /**
     * Handle the Topic "deleted" event.
     */
    public function deleted(Topic $topic): void
    {
        $topic->forum->decrement('count_topics');
    }
}
