<?php

class NewBaseModel extends Illuminate\Database\Eloquent\Model {

    /**
     * Возвращает связь пользователей
     * @return \Granada\ORM|null
     */
    public function user()
    {
        return $this->belongsTo('NewUser', 'user_id');
    }

}
