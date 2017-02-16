<?php

class BaseModel extends Illuminate\Database\Eloquent\Model {

    /**
     * Возвращает связь пользователей
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }

    /**
     * Возвращает объект пользователя
     */
    public function getUser()
    {
        return $this->user ? $this->user : new User();
    }
}
