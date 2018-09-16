<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Transfer
 *
 * @property int id
 */
class Transfer extends BaseModel
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
    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id')->withDefault();
    }
}
