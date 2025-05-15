<?php

declare(strict_types=1);

namespace App\Models;

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
class Ignore extends BaseModel
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
     * Возвращает связь пользователей
     */
    public function ignoring(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ignore_id')->withDefault();
    }
}
