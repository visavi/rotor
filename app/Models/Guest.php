<?php

namespace App\Models;

class Guest extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guest';

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
}
