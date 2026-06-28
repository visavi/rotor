<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BlackList
 *
 * @property int             $id
 * @property string          $type
 * @property string          $value
 * @property int             $user_id
 * @property CarbonImmutable $created_at
 */
class BlackList extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'blacklist';

    /**
     * The name of the "updated at" column.
     */
    public const ?string UPDATED_AT = null;

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
     * Проверяет наличие значения в чёрном списке
     */
    public static function isBlacklisted(string $type, string $value): bool
    {
        return self::query()->where('type', $type)->where('value', $value)->exists();
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
