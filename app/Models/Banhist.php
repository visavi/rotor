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

    /**
     * Возвращает тип бана
     *
     * @return string тип бана
     */
    public function getType()
    {
        switch ($this->type) {
            case self::BAN:
                $type = '<span class="text-danger">Бан</span>';
                break;
            case self::UNBAN:
                $type = '<span class="text-success">Разбан</span>';
                break;
            default:
                $type = '<span class="text-warning">Изменение</span>';
                break;
        }

        return $type;
    }
}
