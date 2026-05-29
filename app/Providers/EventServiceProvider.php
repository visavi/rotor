<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\User;
use App\Observers\CommentObserver;
use App\Observers\UserObserver;
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
        User::observe(UserObserver::class);
    }
}
