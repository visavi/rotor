<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait FeedableTrait
{
    public static function bootFeedableTrait(): void
    {
        static::created(static function ($model) {
            if ($model->shouldBeInFeed()) {
                $model->addToFeed();
            }
        });

        static::updated(static function ($model) {
            if ($model->shouldBeInFeed()) {
                $model->addToFeed();
            } else {
                $model->removeFromFeed();
            }
        });

        static::deleted(static function ($model) {
            $model->removeFromFeed();
        });
    }

    public function shouldBeInFeed(): bool
    {
        return (bool) ($this->active ?? true);
    }

    public function addToFeed(): void
    {
        DB::table('feeds')->updateOrInsert(
            [
                'relate_type' => $this->getMorphClass(),
                'relate_id'   => $this->getKey(),
            ],
            [
                'created_at' => $this->created_at,
            ]
        );

        $this->invalidateFeedCache();
    }

    public function removeFromFeed(): void
    {
        DB::table('feeds')
            ->where('relate_type', $this->getMorphClass())
            ->where('relate_id', $this->getKey())
            ->delete();

        $this->invalidateFeedCache();
    }

    private function invalidateFeedCache(): void
    {
        cache()->increment('feed_version');
    }
}
