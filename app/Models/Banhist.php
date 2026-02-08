<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

/**
 * Class Banhist
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $send_user_id
 * @property string $type
 * @property string $reason
 * @property int    $term
 * @property int    $created_at
 * @property int    $explain
 */
class Banhist extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banhist';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'int',
        ];
    }

    /**
     * Типы банов
     */
    public const BAN = 'ban';    // Бан
    public const UNBAN = 'unban';  // Разбан
    public const CHANGE = 'change'; // Изменение

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает связь пользователя
     */
    public function sendUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'send_user_id')->withDefault();
    }

    /**
     * Возвращает тип бана
     */
    public function getType(): HtmlString
    {
        $type = match ($this->type) {
            self::BAN   => '<span class="text-danger">' . __('main.ban') . '</span>',
            self::UNBAN => '<span class="text-success">' . __('main.unban') . '</span>',
            default     => '<span class="text-warning">' . __('main.changed') . '</span>',
        };

        return new HtmlString($type);
    }
}
