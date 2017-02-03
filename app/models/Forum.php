<?php

class Forum extends BaseModel {

    public static $_table = 'forums';

    /**
     * Возвращает связь родительского форума
     * @return \Granada\ORM|null
     */
    public function parent()
    {
        return $this->belongs_to('Forum', 'parent_id');
    }

    /**
     * Возвращает связь родительского форума
     * @return \Granada\ORM|null
     */
    public function lastTopic()
    {
        return $this->belongs_to('Topic', 'last_topic_id');
    }

    /**
     * Возвращает связь подкатегорий форума
     * @return \Granada\ORM|null
     */
    public function children()
    {
        return $this->has_many('Forum', 'parent_id')->order_by_desc('sort');
    }

    /**
     * Возвращает количество тем в разделе
     * @return \Granada\ORM|null
     */
    public function countTopic()
    {
        return $this->has_one('Topic', 'forum_id')
            ->select_raw('count(*) as count, forum_id')
            ->group_by('forum_id');
    }


    /**
     * Возвращает количество сообщений в разделе
     * @return \Granada\ORM|null
     */
    public function countPost()
    {
        return $this->has_one('Post', 'forum_id')
            ->select_raw('count(*) as count, forum_id')
            ->group_by('forum_id');
    }

    /**
     * Генерирует постраничную навигация для форума
     * @param  array  $topic массив данных
     * @return string       сформированный блок
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
