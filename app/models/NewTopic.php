<?php

class NewTopic extends NewBaseModel {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'topics';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Возвращает количество сообщений в разделе
     */
    public function countPost()
    {
        return $this->hasOne('NewPost', 'topic_id')
            ->select(Capsule::raw('count(*) as count, topic_id'))
            ->groupBy('topic_id');
    }

    /**
     * Возвращает последнее сообщение
     */
    public function lastPost()
    {
        return $this->belongsTo('NewPost', 'last_post_id');
    }

    /**
     * Возвращает раздел форума
     * @return \Granada\ORM|null
     */
/*    public function forum()
    {
        return $this->belongs_to('Forum', 'forum_id');
    }*/

    /**
     * Возвращает иконку в зависимости от статуса
     * @return string иконка топика
     */
/*    public function getIcon()
    {
        if ($this->closed)
            $icon = 'fa-lock';
        elseif ($this->locked)
            $icon = 'fa-thumb-tack';
        else
            $icon = 'fa-folder-open';
        return $icon;
    }*/
}
