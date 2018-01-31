<?php

namespace App\Models;

class Banhist extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banhist';

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
     * Типы банов
     */
    const BAN    = 'ban';    // Бан
    const UNBAN  = 'unban';  // Разбан
    const CHANGE = 'change'; // Изменение

    /**
     * Возвращает связь пользователей
     */
    public function sendUser()
    {
        return $this->belongsTo(User::class, 'send_user_id')->withDefault();
    }
}
