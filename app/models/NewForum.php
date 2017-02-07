<?php

class NewForum extends NewBaseModel {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'forums';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Возвращает связь родительского форума
     */
    public function parent()
    {
        return $this->belongsTo('NewForum', 'parent_id');
    }

    /**
     * Возвращает связь родительского форума
     */
    public function lastTopic()
    {
        return $this->belongsTo('NewTopic', 'last_topic_id');
    }

    /**
     * Возвращает связь подкатегорий форума
     */
    public function children()
    {
        return $this->hasMany('NewForum', 'parent_id')->orderBy('sort', 'desc');
    }

    /**
     * Возвращает количество тем в разделе
     */
    public function countTopic()
    {
        return $this->hasOne('NewTopic', 'forum_id')
            ->select(Capsule::raw('count(*) as count, forum_id'))
            ->groupBy('forum_id');
    }


    /**
     * Возвращает количество сообщений в разделе
     */
    public function countPost()
    {
        return $this->hasOne('NewPost', 'forum_id')
            ->select(Capsule::raw('count(*) as count, forum_id'))
            ->groupBy('forum_id');
    }

    /**
     * Возвращает последнюю тему
     * @return mixed|NewTopic
     */
    public function getLastTopic()
    {
        return $this->lastTopic ? $this->lastTopic : new NewTopic();
    }

    /**
     * Генерирует постраничную навигация для форума
     * @param  array  $topic массив данных
     * @return string        сформированный блок
     */
    public static function pagination($topic)
    {
        if ($topic->countPost->count) {

            $pages = [];
            $link = '/topic/'.$topic['id'];

            $pg_cnt = ceil($topic->countPost->count / App::setting('forumpost'));

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
                    $pages[] = array(
                        'separator' => true,
                        'name' => ' ... ',
                    );
                }

                $pages[] = array(
                    'page' => $pg_cnt,
                    'title' => $pg_cnt.' страница',
                    'name' => $pg_cnt,
                );
            }

            App::view('forum._pagination', compact('pages', 'link'));
        }
    }
}
