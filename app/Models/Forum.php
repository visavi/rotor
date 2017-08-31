<?php

namespace App\Models;

class Forum extends BaseModel
{
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
        return $this->belongsTo('Forum', 'parent_id');
    }

    /**
     * Возвращает связь родительского форума
     */
    public function lastTopic()
    {
        return $this->belongsTo('Topic', 'last_topic_id');
    }

    /**
     * Возвращает связь подкатегорий форума
     */
    public function children()
    {
        return $this->hasMany('Forum', 'parent_id')->orderBy('sort', 'desc');
    }

    /**
     * Возвращает последнюю тему
     * @return mixed|Topic
     */
    public function getLastTopic()
    {
        return $this->lastTopic ? $this->lastTopic : new Topic();
    }

    /**
     * Генерирует постраничную навигация для форума
     * @param  array  $topic массив данных
     * @return string        сформированный блок
     */
    public static function pagination($topic)
    {
        if ($topic->posts) {

            $pages = [];
            $link = '/topic/'.$topic->id;

            $pg_cnt = ceil($topic->posts / setting('forumpost'));

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

            view('forum._pagination', compact('pages', 'link'));
        }
    }
}
