<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Down;
use App\Models\News;
use Illuminate\Support\Facades\DB;

class Restatement
{
    public static array $handlers = [];

    public static function boot(): void
    {
        static::register('forums', function () {
            DB::update('update topics set count_posts = (select count(*) from posts where topics.id = posts.topic_id)');
            DB::update('update forums set count_topics = (select count(*) from topics where forums.id = topics.forum_id)');
            DB::update('update forums set count_posts = (select coalesce(sum(count_posts), 0) from topics where forums.id = topics.forum_id)');
        });

        static::register('loads', function () {
            DB::update('update loads set count_downs = (select count(*) from downs where loads.id = downs.category_id and active = true)');
            DB::update('update downs set count_comments = (select count(*) from comments where relate_type = "' . Down::$morphName . '" and downs.id = comments.relate_id)');
        });

        static::register('news', function () {
            DB::update('update news set count_comments = (select count(*) from comments where relate_type = "' . News::$morphName . '" and news.id = comments.relate_id)');
        });

        static::register('votes', function () {
            DB::update('update votes set count = (select coalesce(sum(result), 0) from voteanswer where votes.id = voteanswer.vote_id)');
        });
    }

    public static function register(string $mode, callable $callback): void
    {
        static::$handlers[$mode] = $callback;
    }

    public static function run(string|array $mode): void
    {
        foreach ((array) $mode as $m) {
            if (isset(static::$handlers[$m])) {
                (static::$handlers[$m])();
            }
        }
    }
}
