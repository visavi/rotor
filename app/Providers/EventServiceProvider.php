<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Post;
use App\Observers\ArticleObserver;
use App\Observers\PostObserver;
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
        Post::observe(PostObserver::class);
    }
}
