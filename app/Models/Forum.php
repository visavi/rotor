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
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Возвращает связь родительского форума
     */
    public function parent()
    {
        return $this->belongsTo(Forum::class, 'parent_id');
    }

    /**
     * Возвращает связь родительского форума
     */
    public function lastTopic()
    {
        return $this->belongsTo(Topic::class, 'last_topic_id');
    }

    /**
     * Возвращает связь подкатегорий форума
     */
    public function children()
    {
        return $this->hasMany(Forum::class, 'parent_id')->orderBy('sort', 'desc');
    }

    /**
     * Возвращает последнюю тему
     * @return mixed|Topic
     */
    public function getLastTopic()
    {
        return $this->lastTopic ? $this->lastTopic : new Topic();
    }
}
