<?php

namespace App\Providers;

use App\Models\Comment;
use App\Observers\CommentObserver;
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
        Comment::observe(CommentObserver::class);
    }
}
