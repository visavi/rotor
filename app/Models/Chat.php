<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Chat
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $text
 * @property string $ip
 * @property string $brow
 * @property int    $created_at
 * @property int    $edit_user_id
 * @property int    $updated_at
 */
class Chat extends BaseModel
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
     * Возвращает связь пользователей
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }
}
