<?php

namespace App\Models;

class Post extends BaseModel
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
     * Возвращает связь пользователей
     */
    public function editUser()
    {
        return $this->belongsTo(User::class, 'edit_user_id');
    }

    /**
     * Возвращает объект пользователя
     */
    public function getEditUser()
    {
        return $this->editUser ? $this->editUser : new User();
    }

    /**
     * Возвращает топик
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files()
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает модель топика
     */
    public function getTopic()
    {
        return $this->topic ? $this->topic : new Topic();
    }
}
