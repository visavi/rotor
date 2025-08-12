<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Blog;

class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        $article->category->restatement();
        clearCache(['statArticles', 'recentArticles', 'ArticleFeed']);
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        if ($article->isDirty('category_id')) {
            $oldCategoryId = $article->getOriginal('category_id');
            $newCategoryId = $article->category_id;

            if ($oldCategoryId !== $newCategoryId) {
                $oldCategory = Blog::query()->find($oldCategoryId);
                $oldCategory?->restatement();
            }

            $article->category->restatement();
        }

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

        clearCache(['statArticles', 'recentArticles', 'ArticleFeed']);
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        $article->category->restatement();
        clearCache(['statArticles', 'recentArticles', 'ArticleFeed']);
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
