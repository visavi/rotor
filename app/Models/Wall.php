<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Wall
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $author_id
 * @property string $text
 * @property int    $created_at
 */
class Wall extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Morph name
     */
    public static string $morphName = 'walls';

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'author_id' => 'int',
            'user_id'   => 'int',
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
     * Возвращает связь владельца
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withDefault();
    }
}
