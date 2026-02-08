<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Guestbook;
use App\Models\Item;
use App\Models\Message;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
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
            Down::$morphName      => Down::class,
            Article::$morphName   => Article::class,
            Photo::$morphName     => Photo::class,
            Offer::$morphName     => Offer::class,
            News::$morphName      => News::class,
            Topic::$morphName     => Topic::class,
            Post::$morphName      => Post::class,
            Guestbook::$morphName => Guestbook::class,
            Message::$morphName   => Message::class,
            Wall::$morphName      => Wall::class,
            Comment::$morphName   => Comment::class,
            Vote::$morphName      => Vote::class,
            Item::$morphName      => Item::class,
            User::$morphName      => User::class,
        ]);
    }
}
