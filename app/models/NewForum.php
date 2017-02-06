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

}
