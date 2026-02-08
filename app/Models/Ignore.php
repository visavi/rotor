<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Ignore
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $ignore_id
 * @property string $text
 * @property int    $created_at
 */
class Ignore extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'ignoring';

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
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает связь пользователя
     */
    public function ignoring(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ignore_id')->withDefault();
    }
}
