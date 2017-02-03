<?php

class Topic extends BaseModel
{

    public static $_table = 'topics';

    /**
     * Возвращает количество сообщений в теме
     * @return \Granada\ORM|null
     */
    public function countPost() {
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
     * Возвращает иконку в зависимости от статуса
     * @return string иконка топика
     */
    public function getIcon()
    {
        if ($this->closed)
            $icon = 'fa-lock';
        elseif ($this->locked)
            $icon = 'fa-thumb-tack';
        else
            $icon = 'fa-folder-open';
        return $icon;
    }
}
