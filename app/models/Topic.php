<?php

class Topic extends BaseModel
{

    public static $_table = 'topics';

    /**
     * Возвращает связь пользователей
     * @return \Granada\ORM|null
     */
    public function user()
    {
        return $this->belongs_to('User', 'user_id');
    }

    /**
     * Возвращает количество сообщений в теме
     * @return \Granada\ORM|null
     */
    public function CountPost() {
        return $this->has_one('Post', 'topic_id')
            ->select_raw('count(*) as count, topic_id')
            ->group_by('topic_id');
    }

    /**
     * Возвращает последнее сообщение
     * @return \Granada\ORM|null
     */
    public function lastPost()
    {
        return $this->belongs_to('Post', 'last_post_id');
    }

    /**
     * Возвращает раздел форума
     * @return \Granada\ORM|null
     */
    public function forum()
    {
        return $this->belongs_to('Forum', 'forum_id');
    }

    /**
     * Возвращает объект пользователя
     * @return \Granada\ORM
     */
    public function getUser()
    {
        return $this->user ? $this->user : $this->factory('User');
    }
}
