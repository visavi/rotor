<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BaseModel
 *
 * @property User user
 * @method increment(string $field, $amount = 1, array $extra = [])
 * @method decrement(string $field, $amount = 1, array $extra = [])
 * @package App\Models
 */
class BaseModel extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'int',
    ];

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает логин пользователя
     *
     * @param string $value
     * @return string
     */
    public function getLoginAttribute($value): string
    {
        return $value ?? setting('deleted_user');
    }
}
