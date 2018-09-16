<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Banhist
 *
 * @property int id
 */
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
    public const BAN    = 'ban';    // Бан
    public const UNBAN  = 'unban';  // Разбан
    public const CHANGE = 'change'; // Изменение

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function sendUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'send_user_id')->withDefault();
    }

    /**
     * Возвращает тип бана
     *
     * @return string тип бана
     */
    public function getType(): string
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
