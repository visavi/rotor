<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Topic;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use App\Observers\PostObserver;
use App\Observers\TopicObserver;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Article::observe(ArticleObserver::class);
        Comment::observe(CommentObserver::class);
        Post::observe(PostObserver::class);
        Topic::observe(TopicObserver::class);
    }
}
