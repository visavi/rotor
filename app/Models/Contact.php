<?php

namespace App\Models;

class Contact extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact';

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
    public function contactor()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    /**
     * Возвращает объект пользователя
     */
    public function getContact()
    {
        return $this->contactor ? $this->contactor : new User();
    }
}
