<?php

class Guest extends BaseModel {

    /**
     * Возвращает пользователя
     * @return \Granada\ORM|null
     */
    public function user()
    {
        return $this->belongs_to('User', 'user_id');
    }

    /**
     * Возвращает объект пользователя
     * @return User
     */
    public function getUser()
    {
        return $this->user ? $this->user : $this->factory('User');
    }
}
