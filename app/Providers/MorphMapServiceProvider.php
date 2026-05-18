<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Guestbook;
use App\Models\Message;
use App\Models\News;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use App\Models\Wall;
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
            News::$morphName      => News::class,
            Topic::$morphName     => Topic::class,
            Post::$morphName      => Post::class,
            Guestbook::$morphName => Guestbook::class,
            Message::$morphName   => Message::class,
            Wall::$morphName      => Wall::class,
            Comment::$morphName   => Comment::class,
            Vote::$morphName      => Vote::class,
            User::$morphName      => User::class,
        ]);
    }
}
