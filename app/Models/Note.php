<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Note
 *
 * @property int id
 */
class Note extends BaseModel
{
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
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }
}
