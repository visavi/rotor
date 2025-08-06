<?php

namespace App\Observers;

use App\Models\Article;

class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        if ($article->wasChanged('active')) {
            $user = $article->user;
            $pointAmount = setting('blog_point');
            $moneyAmount = setting('blog_money');

            if ($article->active) {
                $user->increment('point', $pointAmount);
                $user->increment('money', $moneyAmount);
            } else {
                $user->decrement('point', min($pointAmount, $user->point));
                $user->decrement('money', min($moneyAmount, $user->money));
            }
        }
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "force deleted" event.
     */
    public function forceDeleted(Article $article): void
    {
        //
    }
}
