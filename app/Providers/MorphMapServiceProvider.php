<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider
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
        Relation::enforceMorphMap([
            Message::$morphName => Message::class,
            Comment::$morphName => Comment::class,
            User::$morphName    => User::class,
        ]);
    }
}
