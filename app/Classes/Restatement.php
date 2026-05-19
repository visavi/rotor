<?php

declare(strict_types=1);

namespace App\Classes;

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
