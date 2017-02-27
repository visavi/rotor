<?php

class Topic extends BaseModel {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Возвращает последнее сообщение
     */
    public function lastPost()
    {
        return $this->belongsTo('Post', 'last_post_id');
    }

    /**
     * Возвращает раздел форума
     */
    public function forum()
    {
        return $this->belongsTo('Forum', 'forum_id');
    }

    /**
     * Возвращает закладку
     */
/*    public function bookmark()
    {
        return $this->hasOne('Bookmark');
    }*/

    /**
     * Возвращает последнее сообщение
     */
    public function getLastPost()
    {
        return $this->lastPost ? $this->lastPost : new Post();
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
