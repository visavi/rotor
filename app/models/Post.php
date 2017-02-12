<?php

class Post extends BaseModel {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    /**
     * Возвращает топик
     */
    public function topic()
    {
        return $this->belongsTo('Topic', 'topic_id');
    }

    /**
     * Получает голоса за посты
     */
    public function polling()
    {
        return $this->morphOne('Polling', 'relate')->where('user_id', App::getUserId());
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files()
    {
        return $this->hasMany('FileForum');
    }
}
