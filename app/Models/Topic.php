<?php

namespace App\Models;

class Topic extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Возвращает сообщения
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'topic_id');
    }

    /**
     * Возвращает закладки
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'topic_id');
    }

    /**
     * Возвращает голосование
     */
    public function vote()
    {
        return $this->hasOne(Vote::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает последнее сообщение
     */
    public function lastPost()
    {
        return $this->belongsTo(Post::class, 'last_post_id')->withDefault();
    }

    /**
     * Возвращает раздел форума
     */
    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id')->withDefault();
    }

    /**
     * Возвращает иконку в зависимости от статуса
     *
     * @return string иконка топика
     */
    public function getIcon()
    {
        if ($this->closed) {
            $icon = 'fa-lock';
        } elseif ($this->locked) {
            $icon = 'fa-thumbtack';
        } else {
            $icon = 'fa-folder-open';
        }

        return $icon;
    }

    /**
     * Генерирует постраничную навигация для форума
     *
     * @param  string url                 $url
     * @return string сформированный блок
     */
    public function pagination($url = '/topic')
    {
        if (! $this->count_posts) {
            return null;
        }

        $pages = [];
        $link = $url . '/' . $this->id;

        $pg_cnt = ceil($this->count_posts / setting('forumpost'));

        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $pg_cnt) {
                $pages[] = [
                    'page' => $i,
                    'title' => $i.' страница',
                    'name' => $i,
                ];
            }
        }

        if (5 < $pg_cnt) {

            if (6 < $pg_cnt) {
                $pages[] = [
                    'separator' => true,
                    'name' => ' ... ',
                ];
            }

            $pages[] = [
                'page' => $pg_cnt,
                'title' => $pg_cnt.' страница',
                'name' => $pg_cnt,
            ];
        }

        return view('forum/_pagination', compact('pages', 'link'));
    }

    /**
     * Пересчет темы
     *
     * @return void
     */
    public function restatement(): void
    {
        $lastPost = Post::query()
            ->where('topic_id', $this->id)
            ->orderBy('updated_at', 'desc')
            ->first();

        $countPosts = Post::query()->where('topic_id', $this->id)->count();

        $this->update([
            'count_posts'  => $countPosts,
            'last_post_id' => $lastPost ? $lastPost->id : 0,
        ]);

        $this->forum->restatement();
    }
}
